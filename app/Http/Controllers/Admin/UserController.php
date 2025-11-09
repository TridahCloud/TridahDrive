<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;

class UserController extends Controller
{
    /**
     * Update a user account from the admin console.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $attributes = $request->validated();

        // Ensure boolean casting for checkbox input
        $attributes['is_admin'] = (bool) ($attributes['is_admin'] ?? false);

        $user->fill($attributes);
        $user->save();

        return Redirect::route('admin.dashboard')
            ->with('status', __('User updated successfully.'));
    }

    /**
     * Send a password reset email to the given user.
     */
    public function sendPasswordReset(User $user): RedirectResponse
    {
        if (!$user->email) {
            return Redirect::back()
                ->withErrors(['email' => __('Cannot send reset email because the user does not have an email address.')]);
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            return Redirect::back()
                ->withErrors(['email' => __($status)]);
        }

        return Redirect::back()
            ->with('status', __('Password reset email sent to :email.', ['email' => $user->email]));
    }
}

