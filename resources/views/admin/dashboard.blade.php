@extends('layouts.dashboard')

@section('title', 'Admin Console')

@push('styles')
<style>
.admin-user-accordion {
        margin-top: 1rem;
    }

    .admin-user-accordion .accordion-item {
        background: linear-gradient(145deg, rgba(28, 28, 40, 0.92), rgba(20, 20, 32, 0.9));
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 0.85rem;
        margin-bottom: 0.75rem;
        overflow: hidden;
        box-shadow: 0 18px 35px rgba(0, 0, 0, 0.35);
    }

    .admin-user-accordion .accordion-item:first-of-type {
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
    }

    .admin-user-accordion .accordion-button {
        background: transparent;
        color: rgba(255, 255, 255, 0.92);
        padding: 1.1rem 1.4rem;
        backdrop-filter: blur(6px);
    }

    .admin-user-accordion .accordion-header {
        padding-right: 1.4rem;
    }

    .admin-user-accordion .accordion-header form {
        margin: 0;
    }

    .admin-user-accordion .usage-bar {
        height: 0.45rem;
        background-color: rgba(255, 255, 255, 0.08);
        border-radius: 999px;
        overflow: hidden;
    }

    .admin-user-accordion .usage-bar-fill {
        height: 100%;
        background: linear-gradient(135deg, rgba(49, 216, 178, 0.9), rgba(49, 216, 178, 0.6));
    }

    .admin-user-accordion .accordion-button:not(.collapsed) {
        color: #31d8b2;
        box-shadow: inset 0 0 0 1px rgba(49, 216, 178, 0.25);
        background: linear-gradient(135deg, rgba(49, 216, 178, 0.12), rgba(49, 216, 178, 0.05));
    }

    .admin-user-accordion .accordion-body {
        background: rgba(15, 15, 25, 0.95);
        color: rgba(255, 255, 255, 0.88);
        padding: 1.4rem;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
    }

    .admin-user-accordion .accordion-item:last-of-type {
        margin-bottom: 0;
    }

    .admin-user-accordion form .form-label {
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
    }

    .admin-user-accordion form .form-control,
    .admin-user-accordion form .form-select {
        background-color: rgba(18, 18, 28, 0.85);
        border-color: rgba(255, 255, 255, 0.12);
        color: rgba(255, 255, 255, 0.9);
    }

    .admin-user-accordion form .form-control::placeholder,
    .admin-user-accordion form .form-select::placeholder {
        color: rgba(255, 255, 255, 0.45);
    }

    .admin-user-accordion form .form-control:focus,
    .admin-user-accordion form .form-select:focus {
        border-color: rgba(49, 216, 178, 0.45);
        box-shadow: 0 0 0 0.2rem rgba(49, 216, 178, 0.15);
        color: rgba(255, 255, 255, 0.95);
    }

    .admin-user-accordion form .form-check-label {
        color: rgba(255, 255, 255, 0.85);
    }

    .admin-user-accordion form .form-check-input {
        background-color: rgba(20, 20, 32, 0.9);
        border-color: rgba(255, 255, 255, 0.25);
    }

    .admin-user-accordion form .form-check-input:checked {
        background-color: #31d8b2;
        border-color: #31d8b2;
    }

    .admin-user-accordion .btn-outline-primary.btn-sm {
        color: #31d8b2;
        border-color: rgba(49, 216, 178, 0.4);
    }

    .admin-user-accordion .btn-outline-primary.btn-sm:hover {
        background-color: rgba(49, 216, 178, 0.1);
        border-color: rgba(49, 216, 178, 0.6);
        color: #31d8b2;
    }

    .admin-user-accordion .btn-primary {
        background-color: #31d8b2;
        border-color: #31d8b2;
        color: #0b0d17;
    }

    .admin-user-accordion .btn-primary:hover {
        background-color: #26b999;
        border-color: #26b999;
    }

    [data-theme="light"] .admin-user-accordion .accordion-item {
        background: linear-gradient(135deg, #ffffff, #f6fbff);
        border: 1px solid rgba(15, 23, 42, 0.05);
        box-shadow: 0 18px 30px rgba(15, 23, 42, 0.08);
    }

    [data-theme="light"] .admin-user-accordion .accordion-button:not(.collapsed) {
        background-color: rgba(49, 216, 178, 0.1);
        color: #0f172a;
    }

    [data-theme="light"] .admin-user-accordion .accordion-body {
        background-color: #f8fafc;
        color: #0f172a;
    }

    [data-theme="light"] .admin-user-accordion .usage-bar {
        background-color: rgba(15, 23, 42, 0.08);
    }

    [data-theme="zen"] .admin-user-accordion .accordion-item {
        background: linear-gradient(135deg, #fbfaf4, rgba(251, 250, 244, 0.92));
        border-color: rgba(107, 144, 128, 0.2);
        box-shadow: 0 18px 30px rgba(36, 50, 56, 0.15);
    }

    [data-theme="zen"] .admin-user-accordion .accordion-button:not(.collapsed) {
        background-color: rgba(107, 144, 128, 0.12);
        color: #2f3e46;
    }

    [data-theme="zen"] .admin-user-accordion .accordion-body {
        background-color: rgba(107, 144, 128, 0.08);
        color: #2f3e46;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-6 mb-0 brand-teal">
                <i class="fas fa-shield-halved me-2"></i>{{ __('Admin Console') }}
            </h1>
            <p class="text-muted mb-0">{{ __('Site-wide insights and user management tools for TridahDrive administrators.') }}</p>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>{{ __('Something went wrong') }}</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $formatBytes = static function (?int $bytes): string {
            if (is_null($bytes) || $bytes <= 0) {
                return '0 B';
            }

            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $power = (int) floor(log($bytes, 1024));
            $power = min($power, count($units) - 1);
            $value = $bytes / (1024 ** $power);

            return number_format($value, $value >= 10 || $power === 0 ? 0 : 2) . ' ' . $units[$power];
        };
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted small mb-1">{{ __('Total Users') }}</p>
                    <h3 class="mb-3">{{ number_format($metrics['totals']['users']) }}</h3>
                    <p class="text-muted mb-0">
                        <span class="fw-semibold text-success">{{ number_format($metrics['totals']['new_last_30_days']) }}</span>
                        {{ __('new signups in the past 30 days') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted small mb-1">{{ __('Active Users') }}</p>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span>{{ __('Last 24 hours') }}</span>
                            <span class="fw-semibold">{{ number_format($metrics['active']['day']) }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span>{{ __('Last 7 days') }}</span>
                            <span class="fw-semibold">{{ number_format($metrics['active']['week']) }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-1">
                            <span>{{ __('Last 30 days') }}</span>
                            <span class="fw-semibold">{{ number_format($metrics['active']['month']) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-uppercase text-muted small mb-1">{{ __('Storage Usage') }}</p>
                            <h3 class="mb-0">{{ $formatBytes($storage['total_bytes']) }}</h3>
                        </div>
                        @unless($storage['detected'])
                            <span class="badge bg-warning text-dark">{{ __('No data') }}</span>
                        @endunless
                    </div>
                    @if ($storage['detected'])
                        <div class="mt-auto">
                            <p class="text-muted small mb-2">{{ __('Top storage consumers') }}</p>
                            <ul class="list-unstyled mb-0">
                                @foreach ($storage['per_user']->take(5) as $entry)
                                    <li class="d-flex justify-content-between border-bottom py-1">
                                        <span>{{ optional($entry['user'])->name ?? __('Unknown User') }}</span>
                                        <span class="fw-semibold">{{ $formatBytes($entry['bytes']) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <p class="text-muted small mb-0">
                            {{ __('Upload task or transaction attachments to start tracking per-user storage usage.') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-column flex-lg-row justify-content-lg-between align-items-lg-center gap-2">
            <div>
                <h2 class="h5 mb-0">{{ __('User Management') }}</h2>
                <p class="text-muted mb-0 small">{{ __('Update account details, promote admins, or send password reset links.') }}</p>
            </div>
            <span class="badge bg-primary-subtle text-primary">
                {{ trans_choice(':count total user|:count total users', $metrics['totals']['users'], ['count' => $metrics['totals']['users']]) }}
            </span>
        </div>

        @php
            $hasUserFilters = filled($filters['search'] ?? null)
                || in_array($filters['role'] ?? '', ['admin', 'user'], true)
                || (($filters['sort'] ?? 'name_asc') !== 'name_asc')
                || (($filters['per_page'] ?? 25) !== 25);
        @endphp

        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="admin-user-search">{{ __('Search') }}</label>
                    <input
                        type="search"
                        name="search"
                        id="admin-user-search"
                        value="{{ $filters['search'] }}"
                        class="form-control"
                        placeholder="{{ __('Search by name or email') }}"
                    >
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label" for="admin-user-role">{{ __('Role') }}</label>
                    <select name="role" id="admin-user-role" class="form-select">
                        <option value="">{{ __('All roles') }}</option>
                        <option value="admin" @selected(($filters['role'] ?? '') === 'admin')>{{ __('Site admins') }}</option>
                        <option value="user" @selected(($filters['role'] ?? '') === 'user')>{{ __('Standard users') }}</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label" for="admin-user-sort">{{ __('Sort') }}</label>
                    <select name="sort" id="admin-user-sort" class="form-select">
                        @foreach ($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'name_asc') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label" for="admin-user-per-page">{{ __('Per page') }}</label>
                    <select name="per_page" id="admin-user-per-page" class="form-select" onchange="this.form.submit()">
                        @foreach ([15, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected(($filters['per_page'] ?? 25) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>{{ __('Apply filters') }}
                    </button>
                    @if ($hasUserFilters)
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-rotate me-1"></i>{{ __('Reset') }}
                        </a>
                    @endif
                </div>
            </form>

            <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-2 mt-3 small text-muted">
                <div>
                    @if ($users->total() > 0)
                        {{ __('Showing :from–:to of :total users', [
                            'from' => $users->firstItem(),
                            'to' => $users->lastItem(),
                            'total' => number_format($users->total()),
                        ]) }}
                    @else
                        {{ __('No users found') }}
                    @endif
                </div>
                @if ($hasUserFilters)
                    <div>
                        <span class="badge bg-dark-subtle text-body">{{ __('Filters active') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="accordion accordion-flush admin-user-accordion" id="adminUsersAccordion">
            @forelse ($users as $user)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="admin-user-heading-{{ $user->id }}">
                        <div class="d-flex flex-column flex-lg-row w-100 align-items-stretch gap-2">
                            <button class="accordion-button collapsed flex-grow-1 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#admin-user-{{ $user->id }}" aria-expanded="false" aria-controls="admin-user-{{ $user->id }}">
                                <div class="d-flex flex-column flex-lg-row w-100 justify-content-between align-items-lg-center gap-2">
                                    <div>
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                        <div class="text-muted small">{{ $user->email }}</div>
                                    @php
                                        $userBytes = $storage['per_user_map'][$user->id] ?? 0;
                                        $usagePercent = $storage['total_bytes'] > 0 ? min(100, round(($userBytes / $storage['total_bytes']) * 100)) : 0;
                                    @endphp
                                    <div class="mt-2">
                                        <div class="d-flex justify-content-between small text-muted">
                                            <span>{{ __('Storage') }}</span>
                                            <span>{{ $formatBytes($userBytes) }}</span>
                                        </div>
                                        <div class="usage-bar mt-1" aria-hidden="true">
                                            <div class="usage-bar-fill" style="width: {{ $usagePercent }}%;"></div>
                                        </div>
                                        <span class="visually-hidden">
                                            {{ __('Storage usage: :percent% of total tracked storage', ['percent' => $usagePercent]) }}
                                        </span>
                                    </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge {{ $user->is_admin ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $user->is_admin ? __('Site Admin') : __('Standard User') }}
                                        </span>
                                        <small class="text-muted">{{ __('Joined :date', ['date' => $user->created_at?->format('M j, Y') ?? __('N/A')]) }}</small>
                                    </div>
                                </div>
                            </button>
                            <form method="POST" action="{{ route('admin.users.password-reset', $user) }}" class="d-flex align-items-center" onsubmit="return confirm('{{ __('Send a password reset email to :email?', ['email' => $user->email]) }}');">
                                @csrf
                                <button class="btn btn-outline-primary btn-sm" type="submit">
                                    <i class="fas fa-envelope me-1"></i>{{ __('Send Reset') }}
                                </button>
                            </form>
                        </div>
                    </h2>
                    <div id="admin-user-{{ $user->id }}" class="accordion-collapse collapse" aria-labelledby="admin-user-heading-{{ $user->id }}" data-bs-parent="#adminUsersAccordion">
                        <div class="accordion-body">
                            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="row g-3">
                                @csrf
                                @method('PATCH')

                                <div class="col-md-6">
                                    <label for="name-{{ $user->id }}" class="form-label">{{ __('Name') }}</label>
                                    <input type="text" class="form-control" id="name-{{ $user->id }}" name="name" value="{{ old('name', $user->name) }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="email-{{ $user->id }}" class="form-label">{{ __('Email') }}</label>
                                    <input type="email" class="form-control" id="email-{{ $user->id }}" name="email" value="{{ old('email', $user->email) }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="timezone-{{ $user->id }}" class="form-label">{{ __('Timezone') }}</label>
                                    <input type="text" class="form-control" id="timezone-{{ $user->id }}" name="timezone" value="{{ old('timezone', $user->timezone) }}" placeholder="UTC">
                                </div>

                                <div class="col-md-6">
                                    <label for="currency-{{ $user->id }}" class="form-label">{{ __('Currency') }}</label>
                                    <input type="text" class="form-control" id="currency-{{ $user->id }}" name="currency" value="{{ old('currency', $user->currency) }}" placeholder="USD">
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="hidden" name="is_admin" value="0">
                                        <input class="form-check-input" type="checkbox" value="1" id="is_admin-{{ $user->id }}" name="is_admin" @checked(old('is_admin', $user->is_admin))>
                                        <label class="form-check-label" for="is_admin-{{ $user->id }}">
                                            {{ __('Site administrator') }}
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>{{ __('Save Changes') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-users-slash fa-2x mb-3"></i>
                    <p class="mb-0">{{ __('Try adjusting your filters or search criteria.') }}</p>
                </div>
            @endforelse
        </div>

        <div class="card-footer">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection

