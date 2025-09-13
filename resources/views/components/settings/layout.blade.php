<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <!-- 戻るボタン -->
        <div class="mb-4">
            <a href="{{ route('dashboard') }}" wire:navigate
                class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                ダッシュボードに戻る
            </a>
        </div>

        <flux:navlist>
            <!-- 既存の設定項目 -->
            <flux:navlist.group heading="アカウント設定">
                <flux:navlist.item :href="route('settings.profile')" wire:navigate>{{ __('Profile') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('settings.password')" wire:navigate>{{ __('Password') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('settings.appearance')" wire:navigate>{{ __('Appearance') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
