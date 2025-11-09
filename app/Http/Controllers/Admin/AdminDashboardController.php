<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriveItem;
use App\Models\TaskAttachment;
use App\Models\TransactionAttachment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $metrics = $this->buildUserMetrics();
        $storage = $this->buildStorageUsage();
        $perPage = $this->resolvePerPage($request->input('per_page'));
        $users = $this->loadUsersForManagement($request, $perPage);

        return view('admin.dashboard', [
            'metrics' => $metrics,
            'storage' => $storage,
            'users' => $users,
            'filters' => [
                'search' => $request->string('search')->trim()->value(),
                'role' => $request->string('role')->lower()->value(),
                'sort' => $request->string('sort')->lower()->value(),
                'per_page' => $perPage,
            ],
            'sortOptions' => $this->availableSortOptions(),
        ]);
    }

    /**
     * Build high-level user metrics for the dashboard.
     *
     * @return array<string, mixed>
     */
    protected function buildUserMetrics(): array
    {
        $now = Carbon::now();

        return [
            'totals' => [
                'users' => User::count(),
                'new_last_30_days' => User::where('created_at', '>=', $now->copy()->subDays(30))->count(),
            ],
            'active' => [
                'day' => $this->countActiveUsersSince($now->copy()->subDay()),
                'week' => $this->countActiveUsersSince($now->copy()->subWeek()),
                'month' => $this->countActiveUsersSince($now->copy()->subDays(30)),
            ],
        ];
    }

    /**
     * Count active users based on session activity.
     */
    protected function countActiveUsersSince(Carbon $since): int
    {
        return (int) DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $since->timestamp)
            ->distinct()
            ->count('user_id');
    }

    /**
     * Aggregate storage usage from drive items metadata.
     *
     * @return array{
     *     total_bytes: int,
     *     per_user: \Illuminate\Support\Collection<int, array{user: \App\Models\User|null, bytes: int}>,
     *     detected: bool
     * }
     */
    protected function buildStorageUsage(): array
    {
        $totals = [];
        $accumulator = function (?int $userId, int $bytes) use (&$totals): void {
            if ($bytes <= 0) {
                return;
            }

            $key = $userId ?? 'unknown';

            if (!array_key_exists($key, $totals)) {
                $totals[$key] = 0;
            }

            $totals[$key] += $bytes;
        };

        DriveItem::select(['metadata', 'created_by_id'])
            ->lazy()
            ->each(function (DriveItem $item) use ($accumulator): void {
                $size = data_get($item->metadata, 'size')
                    ?? data_get($item->metadata, 'bytes')
                    ?? data_get($item->metadata, 'file_size');

                if (is_string($size) && is_numeric($size)) {
                    $size = (float) $size;
                }

                if (!is_numeric($size)) {
                    return;
                }

                $size = (int) round($size);
                if ($size <= 0) {
                    return;
                }

                $accumulator($item->created_by_id, $size);
            });

        TaskAttachment::query()
            ->select(['uploaded_by', DB::raw('SUM(file_size) as total_size')])
            ->groupBy('uploaded_by')
            ->get()
            ->each(function ($row) use ($accumulator): void {
                $accumulator($row->uploaded_by ? (int) $row->uploaded_by : null, (int) $row->total_size);
            });

        TransactionAttachment::query()
            ->select(['uploaded_by', DB::raw('SUM(file_size) as total_size')])
            ->groupBy('uploaded_by')
            ->get()
            ->each(function ($row) use ($accumulator): void {
                $accumulator($row->uploaded_by ? (int) $row->uploaded_by : null, (int) $row->total_size);
            });

        $numericUserIds = collect(array_keys($totals))
            ->filter(fn ($key): bool => is_numeric($key))
            ->map(fn ($key): int => (int) $key)
            ->unique()
            ->values()
            ->all();

        $users = User::whereIn('id', $numericUserIds)
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $perUser = collect($totals)
            ->map(function (int $bytes, string|int $key) use ($users): array {
                $user = is_numeric($key) ? $users->get((int) $key) : null;

                return [
                    'user' => $user,
                    'bytes' => $bytes,
                    'is_unknown' => !$user,
                ];
            })
            ->sortByDesc('bytes')
            ->values();

        $perUserMap = $perUser
            ->filter(fn (array $entry): bool => $entry['user'] !== null)
            ->mapWithKeys(function (array $entry) {
                /** @var \App\Models\User $user */
                $user = $entry['user'];

                return [$user->id => $entry['bytes']];
            })
            ->toArray();

        $unknownBytes = $perUser
            ->filter(fn (array $entry): bool => $entry['user'] === null)
            ->sum('bytes');

        $totalBytes = array_sum($totals);

        return [
            'total_bytes' => (int) $totalBytes,
            'per_user' => $perUser,
            'per_user_map' => $perUserMap,
            'unknown_bytes' => (int) $unknownBytes,
            'detected' => $totalBytes > 0,
        ];
    }

    /**
     * Load paginated users for admin management actions.
     */
    protected function loadUsersForManagement(Request $request, int $perPage): LengthAwarePaginator
    {
        $search = $request->string('search')->trim()->value();
        $role = $request->string('role')->lower()->value();
        $sort = $request->string('sort')->lower()->value();

        $query = User::query()
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->when($role === 'admin', fn ($query): mixed => $query->where('is_admin', true))
            ->when($role === 'user', fn ($query): mixed => $query->where('is_admin', false));

        $this->applySortOrder($query, $sort);

        return $query
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Normalize and validate the per-page value for pagination.
     */
    protected function resolvePerPage($perPage): int
    {
        $allowed = [15, 25, 50, 100];
        $fallback = 25;

        if (is_string($perPage) && is_numeric($perPage)) {
            $perPage = (int) $perPage;
        }

        if (is_null($perPage)) {
            return $fallback;
        }

        if (!in_array($perPage, $allowed, true)) {
            return $fallback;
        }

        return $perPage;
    }

    /**
     * Apply an allowed sort order to the users query.
     */
    protected function applySortOrder(Builder $query, ?string $sort): void
    {
        $sort = $sort ?: 'name_asc';

        switch ($sort) {
            case 'name_desc':
                $query->orderByDesc('name')->orderBy('email');
                break;
            case 'created_desc':
                $query->orderByDesc('created_at')->orderBy('name');
                break;
            case 'created_asc':
                $query->orderBy('created_at')->orderBy('name');
                break;
            default:
                $query->orderBy('name')->orderBy('email');
                break;
        }
    }

    /**
     * Provide the available sort options for the UI.
     *
     * @return array<string, string>
     */
    protected function availableSortOptions(): array
    {
        return [
            'name_asc' => __('Name (A–Z)'),
            'name_desc' => __('Name (Z–A)'),
            'created_desc' => __('Newest first'),
            'created_asc' => __('Oldest first'),
        ];
    }
}

