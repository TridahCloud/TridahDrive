@section('title', 'Register')
@section('description', 'Create your free TridahDrive account and start managing your business with our all-in-one platform.')
<x-guest-layout>
    <h2 class="text-center mb-4 fw-bold">Create Account</h2>
    <p class="text-center text-muted mb-4">Join us and start building the future with open source.</p>
    
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-3">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div class="mb-3">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="d-grid mb-3">
            <x-primary-button>
                {{ __('Register') }}
            </x-primary-button>
        </div>

        <div class="text-center">
            <span class="text-muted">Already have an account? </span>
            <a href="{{ route('login') }}" class="text-decoration-none">{{ __('Login') }}</a>
        </div>
    </form>
</x-guest-layout>
