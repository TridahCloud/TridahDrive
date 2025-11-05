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

    <div class="mb-3">
        <x-input-label for="currency" :value="__('Currency')" />
        <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
            @foreach(\App\Helpers\CurrencyHelper::getAllCurrencies() as $code => $currency)
                <option value="{{ $code }}" {{ old('currency', $user->currency ?? 'USD') === $code ? 'selected' : '' }}>
                    {{ $currency['name'] }} ({{ $currency['symbol'] }})
                </option>
            @endforeach
        </select>
        <small class="text-muted">Currency used for your personal drives</small>
        <x-input-error :messages="$errors->get('currency')" />
    </div>

    <div class="mb-3">
        <x-input-label for="timezone" :value="__('Timezone')" />
        <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone">
            <option value="">Default (UTC)</option>
            @foreach(\App\Helpers\TimezoneHelper::getCommonTimezones() as $tz => $label)
                <option value="{{ $tz }}" {{ old('timezone', $user->timezone ?? '') === $tz ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">All dates and times will be displayed in your selected timezone</small>
        <x-input-error :messages="$errors->get('timezone')" />
    </div>

    <div class="d-flex align-items-center gap-3">
        <x-primary-button>{{ __('Save') }}</x-primary-button>

        @if (session('status') === 'profile-updated')
            <span class="text-success small">
                <i class="fas fa-check-circle me-1"></i>{{ __('Saved.') }}
            </span>
        @endif
    </div>
</form>
