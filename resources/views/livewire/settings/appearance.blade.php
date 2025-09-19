<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <!-- 背景パターン付きの設定レイアウト -->
    <div
        class="relative overflow-hidden rounded-xl border-2 border-green-200 dark:border-green-700 bg-gradient-to-br from-green-50 to-amber-50 dark:from-green-900 dark:to-amber-900 shadow-xl">
        <!-- 背景パターン -->
        <div class="absolute inset-0 bg-gradient-to-br from-green-400/40 to-amber-400/40"></div>

        <div class="relative z-10 p-8">
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-green-800 dark:text-green-200 mb-3 drop-shadow-sm">外観設定</h2>
                <p class="text-green-600 dark:text-green-300 text-lg font-medium">アカウントの外観設定を更新してください</p>
            </div>

            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
            </flux:radio.group>
        </div>
    </div>
</section>
