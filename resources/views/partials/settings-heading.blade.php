<div class="relative mb-6 w-full">
    <!-- 背景パターン付きのヘッダー -->
    <div
        class="relative overflow-hidden rounded-xl bg-gradient-to-r from-green-800 to-amber-200 p-8 text-white mb-6 shadow-2xl">
        <!-- 背景パターン -->
        <div class="absolute inset-0 bg-gradient-to-br from-green-600/60 to-amber-400/60"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-green-800/85 to-amber-200/85"></div>

        <!-- パンくずナビゲーション -->
        <nav class="relative z-10 flex mb-4" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-4">
                <li>
                    <div>
                        <a href="{{ route('dashboard') }}" wire:navigate
                            class="text-indigo-100 hover:text-white transition-colors duration-200">
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
                        <svg class="h-5 w-5 text-indigo-200" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-4 text-sm font-medium text-indigo-100">設定</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- ヘッダー -->
        <div class="relative z-10">
            <h1 class="text-4xl font-bold mb-3 text-white drop-shadow-lg">{{ __('Settings') }}</h1>
            <p class="text-green-100 text-xl drop-shadow-md">{{ __('Manage your profile and account settings') }}</p>
        </div>
    </div>
</div>
