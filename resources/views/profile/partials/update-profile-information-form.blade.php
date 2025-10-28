<form method="post" action="{{ route('profile.update') }}" class="space-y-4">
    @csrf
    @method('patch')

    <div class="mb-3">
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="mb-3">
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
        <x-input-error :messages="$errors->get('email')" />

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="mt-2">
                <p class="text-muted small">
                    {{ __('Your email address is unverified.') }}
                    <button form="send-verification" class="btn btn-link p-0 text-decoration-none">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success mt-2">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </div>
                @endif
            </div>
        @endif
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-none">
        @csrf
    </form>

    <div class="d-flex align-items-center gap-3">
        <x-primary-button>{{ __('Save') }}</x-primary-button>

        @if (session('status') === 'profile-updated')
            <span class="text-success small">
                <i class="fas fa-check-circle me-1"></i>{{ __('Saved.') }}
            </span>
        @endif
    </div>
</form>
