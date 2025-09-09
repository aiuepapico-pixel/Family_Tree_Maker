<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('A reset link will be sent if the account exists.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('パスワードを忘れた場合')" :description="__('メールアドレスを入力してパスワードリセットリンクをお送りします')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <!-- Email Address -->
        <div class="space-y-2">
            <label for="email" class="auth-label">
                {{ __('メールアドレス') }}
            </label>
            <input wire:model="email" id="email" type="email" required autofocus placeholder="email@example.com"
                class="auth-input" />
            @error('email')
                <p class="auth-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="auth-button">
            {{ __('パスワードリセットリンクを送信') }}
        </button>
    </form>

    <div class="text-center text-sm">
        <span class="text-sage-600 dark:text-sage-400">{{ __('または') }}</span>
        <a href="{{ route('login') }}" wire:navigate class="auth-link ml-1">
            {{ __('ログイン画面に戻る') }}
        </a>
    </div>
</div>
