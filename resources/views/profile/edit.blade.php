@extends('layouts.dashboard')

@section('title', 'Profile Settings')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <!-- Page Header -->
            <div class="mb-4">
                <h1 class="display-6 mb-0 brand-teal">Profile Settings</h1>
                <p class="text-muted">Manage your account settings and preferences</p>
            </div>

            @if(session('status') === 'profile-updated' || session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success', 'Profile updated successfully!') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>Please fix the following errors:
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Profile Information -->
            <div class="dashboard-card mb-4">
                <h3 class="mb-3 brand-teal">
                    <i class="fas fa-user me-2"></i>{{ __('Profile Information') }}
                </h3>
                <p class="text-muted mb-4">{{ __("Update your account's profile information and email address.") }}</p>

                @include('profile.partials.update-profile-information-form')
            </div>
            
            <!-- Verification Form (moved outside to avoid nested forms) -->
            <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-none">
                @csrf
            </form>

            <!-- Update Password -->
            <div class="dashboard-card mb-4">
                <h3 class="mb-3 brand-teal">
                    <i class="fas fa-lock me-2"></i>{{ __('Update Password') }}
                </h3>
                <p class="text-muted mb-4">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>

                @include('profile.partials.update-password-form')
            </div>

            <!-- Delete Account -->
            <div class="dashboard-card mb-4">
                <h3 class="mb-3 text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ __('Delete Account') }}
                </h3>
                <p class="text-muted mb-4">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}</p>

                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="confirm-user-deletion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Delete Account') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="{{ route('profile.destroy') }}" id="delete-account-form">
                @csrf
                @method('delete')

                <div class="modal-body">
                    <p>{{ __('Are you sure you want to delete your account?') }}</p>
                    <p class="text-muted small">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}</p>

                    <div class="mb-3">
                        <label for="delete_password" class="form-label">{{ __('Password') }}</label>
                        <input type="password" class="form-control" id="delete_password" name="password" required autocomplete="current-password">
                        @if($errors->userDeletion->has('password'))
                            <div class="invalid-feedback d-block">{{ $errors->userDeletion->first('password') }}</div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('Delete Account') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
