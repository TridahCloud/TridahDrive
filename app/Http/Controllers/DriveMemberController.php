<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\User;
use App\Services\DriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriveMemberController extends Controller
{
    public function __construct(
        protected DriveService $driveService
    ) {}

    /**
     * Invite a user to a drive
     */
    public function invite(Request $request, Drive $drive)
    {
        $this->authorize('invite', $drive);

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:admin,member,viewer',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

        try {
            $this->driveService->inviteUser($drive, Auth::user(), $user, $validated['role']);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'User invited successfully!']);
            }

            return redirect()->back()
                ->with('success', 'User invited successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return redirect()->back()
                ->withErrors(['email' => $e->getMessage()]);
        }
    }

    /**
     * Update a member's role
     */
    public function updateRole(Request $request, Drive $drive, User $user)
    {
        $this->authorize('manageMembers', $drive);

        $validated = $request->validate([
            'role' => 'required|in:owner,admin,member,viewer',
        ]);

        try {
            $this->driveService->updateUserRole($drive, Auth::user(), $user, $validated['role']);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Member role updated successfully!']);
            }

            return redirect()->back()->with('success', 'Member role updated successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove a member from a drive
     */
    public function remove(Drive $drive, User $user)
    {
        $this->authorize('manageMembers', $drive);

        try {
            $this->driveService->removeUser($drive, Auth::user(), $user);

            if (request()->expectsJson()) {
                return response()->json(['message' => 'Member removed successfully!']);
            }

            return redirect()->back()->with('success', 'Member removed successfully!');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Allow a user to leave a shared drive
     */
    public function leave(Drive $drive, Request $request)
    {
        $user = Auth::user();

        try {
            $this->driveService->leaveDrive($drive, $user);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'You have left the drive successfully!']);
            }

            return redirect()->route('drives.index')
                ->with('success', 'You have left the drive successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Transfer ownership of a drive
     */
    public function transferOwnership(Request $request, Drive $drive, User $user)
    {
        // Only owner can transfer ownership
        if ($drive->owner_id !== Auth::id()) {
            abort(403, 'Only the drive owner can transfer ownership.');
        }

        $validated = $request->validate([
            'confirm_transfer' => 'required|accepted',
        ]);

        try {
            $this->driveService->transferOwnership($drive, Auth::user(), $user);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Ownership transferred successfully!']);
            }

            return redirect()->back()->with('success', 'Ownership transferred successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
