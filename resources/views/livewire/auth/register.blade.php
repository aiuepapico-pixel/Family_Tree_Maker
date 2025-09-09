<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('アカウント新規登録')" :description="__('以下の情報を入力してアカウントを作成してください')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <div class="space-y-2">
            <label for="name" class="block text-sm font-medium text-forest-700 dark:text-forest-200">
                {{ __('お名前') }}
            </label>
            <input wire:model="name" id="name" type="text" required autofocus autocomplete="name"
                placeholder="{{ __('フルネーム') }}"
                class="w-full px-4 py-3 rounded-xl border border-sage-300 dark:border-sage-600 bg-white dark:bg-forest-800 text-forest-900 dark:text-forest-100 placeholder-sage-500 dark:placeholder-sage-400 focus:ring-2 focus:ring-forest-500 focus:border-transparent transition-colors duration-200" />
            @error('name')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="space-y-2">
            <label for="email" class="block text-sm font-medium text-forest-700 dark:text-forest-200">
                {{ __('メールアドレス') }}
            </label>
            <input wire:model="email" id="email" type="email" required autocomplete="email"
                placeholder="email@example.com"
                class="w-full px-4 py-3 rounded-xl border border-sage-300 dark:border-sage-600 bg-white dark:bg-forest-800 text-forest-900 dark:text-forest-100 placeholder-sage-500 dark:placeholder-sage-400 focus:ring-2 focus:ring-forest-500 focus:border-transparent transition-colors duration-200" />
            @error('email')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <label for="password" class="block text-sm font-medium text-forest-700 dark:text-forest-200">
                {{ __('パスワード') }}
            </label>
            <input wire:model="password" id="password" type="password" required autocomplete="new-password"
                placeholder="{{ __('パスワード') }}"
                class="w-full px-4 py-3 rounded-xl border border-sage-300 dark:border-sage-600 bg-white dark:bg-forest-800 text-forest-900 dark:text-forest-100 placeholder-sage-500 dark:placeholder-sage-400 focus:ring-2 focus:ring-forest-500 focus:border-transparent transition-colors duration-200" />
            @error('password')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="space-y-2">
            <label for="password_confirmation" class="block text-sm font-medium text-forest-700 dark:text-forest-200">
                {{ __('パスワード確認') }}
            </label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password" required
                autocomplete="new-password" placeholder="{{ __('パスワードを再入力') }}"
                class="w-full px-4 py-3 rounded-xl border border-sage-300 dark:border-sage-600 bg-white dark:bg-forest-800 text-forest-900 dark:text-forest-100 placeholder-sage-500 dark:placeholder-sage-400 focus:ring-2 focus:ring-forest-500 focus:border-transparent transition-colors duration-200" />
            @error('password_confirmation')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
            class="w-full bg-gradient-to-r from-forest-600 to-sage-600 hover:from-forest-700 hover:to-sage-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-forest-500 focus:ring-offset-2 dark:focus:ring-offset-forest-900">
            {{ __('アカウントを作成') }}
        </button>
    </form>

    <div class="text-center text-sm">
        <span class="text-sage-600 dark:text-sage-400">{{ __('既にアカウントをお持ちの方は') }}</span>
        <a href="{{ route('login') }}" wire:navigate
            class="text-forest-600 dark:text-forest-400 hover:text-forest-500 dark:hover:text-forest-300 font-medium transition-colors duration-200 ml-1">
            {{ __('ログイン') }}
        </a>
    </div>
</div>
