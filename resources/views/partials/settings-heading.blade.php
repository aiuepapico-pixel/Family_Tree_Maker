<div class="relative mb-6 w-full">
    <!-- パンくずナビゲーション -->
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-4">
            <li>
                <div>
                    <a href="{{ route('dashboard') }}" wire:navigate class="text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                            </path>
                        </svg>
                        <span class="sr-only">ダッシュボード</span>
                    </a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-4 text-sm font-medium text-gray-500">設定</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- ヘッダー -->
    <div class="mb-6">
        <flux:heading size="xl" level="1">{{ __('Settings') }}</flux:heading>
        <flux:subheading size="lg">{{ __('Manage your profile and account settings') }}</flux:subheading>
    </div>

    <flux:separator variant="subtle" />
</div>
