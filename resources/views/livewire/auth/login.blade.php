<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('ログイン')" :description="__('メールアドレスとパスワードを入力してログインしてください')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <div class="space-y-2">
            <label for="email" class="block text-sm font-medium text-forest-700 dark:text-forest-200">
                {{ __('メールアドレス') }}
            </label>
            <input wire:model="email" id="email" type="email" required autofocus autocomplete="email"
                placeholder="email@example.com"
                class="w-full px-4 py-3 rounded-xl border border-sage-300 dark:border-sage-600 bg-white dark:bg-forest-800 text-forest-900 dark:text-forest-100 placeholder-sage-500 dark:placeholder-sage-400 focus:ring-2 focus:ring-forest-500 focus:border-transparent transition-colors duration-200" />
            @error('email')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <div class="flex justify-between items-center">
                <label for="password" class="block text-sm font-medium text-forest-700 dark:text-forest-200">
                    {{ __('パスワード') }}
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate
                        class="text-sm text-sage-600 dark:text-sage-400 hover:text-forest-600 dark:hover:text-forest-300 transition-colors duration-200">
                        {{ __('パスワードを忘れた場合') }}
                    </a>
                @endif
            </div>
            <input wire:model="password" id="password" type="password" required autocomplete="current-password"
                placeholder="{{ __('パスワード') }}"
                class="w-full px-4 py-3 rounded-xl border border-sage-300 dark:border-sage-600 bg-white dark:bg-forest-800 text-forest-900 dark:text-forest-100 placeholder-sage-500 dark:placeholder-sage-400 focus:ring-2 focus:ring-forest-500 focus:border-transparent transition-colors duration-200" />
            @error('password')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input wire:model="remember" id="remember" type="checkbox"
                class="h-4 w-4 text-forest-600 border-sage-300 dark:border-sage-600 rounded focus:ring-forest-500 dark:bg-forest-800" />
            <label for="remember" class="ml-2 block text-sm text-sage-700 dark:text-sage-300">
                {{ __('ログイン状態を保持する') }}
            </label>
        </div>

        <button type="submit"
            class="w-full bg-gradient-to-r from-forest-600 to-sage-600 hover:from-forest-700 hover:to-sage-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-forest-500 focus:ring-offset-2 dark:focus:ring-offset-forest-900">
            {{ __('ログイン') }}
        </button>
    </form>

    @if (Route::has('register'))
        <div class="text-center text-sm">
            <span class="text-sage-600 dark:text-sage-400">{{ __('アカウントをお持ちでない方は') }}</span>
            <a href="{{ route('register') }}" wire:navigate
                class="text-forest-600 dark:text-forest-400 hover:text-forest-500 dark:hover:text-forest-300 font-medium transition-colors duration-200 ml-1">
                {{ __('新規登録') }}
            </a>
        </div>
    @endif
</div>
