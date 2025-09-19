<x-layouts.app :title="__('Dashboard')">
    <div class="space-y-6">
        <!-- ヘッダー -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">ダッシュボード</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">相続関係説明図作成アプリへようこそ</p>
        </div>

        <!-- クイックアクション -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('family-trees.create') }}" wire:navigate
                class="group relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 hover:shadow-md transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-600">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">新しい家系図</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">家系図を作成して開始</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('family-trees.index') }}" wire:navigate
                class="group relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 hover:shadow-md transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-600">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">家系図一覧</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">既存の家系図を管理</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('settings.profile') }}" wire:navigate
                class="group relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 hover:shadow-md transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-600">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">設定</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">アカウント設定を管理</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- 統計情報 -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">総家系図数</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">0</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">登録人数</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">0</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">今月の作成</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">0</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">PDF出力</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 最近の家系図 -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">最近の家系図</h2>
            </div>
            <div class="p-6">
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">家系図がありません</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">新しい家系図を作成して開始しましょう。</p>
                    <div class="mt-6">
                        <a href="{{ route('family-trees.create') }}" wire:navigate
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            新しい家系図を作成
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
