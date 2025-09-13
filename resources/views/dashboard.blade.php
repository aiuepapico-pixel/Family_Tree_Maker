<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <!-- ウェルカムメッセージ -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl p-6 text-white">
            <h1 class="text-2xl font-bold mb-2">相続関係説明図作成アプリ</h1>
            <p class="text-blue-100">家系図を作成して、法定相続関係を可視化しましょう</p>
        </div>

        <!-- クイックアクション -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('family-trees.create') }}" wire:navigate
                class="group relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">新しい家系図</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">家系図を作成して開始</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('family-trees.index') }}" wire:navigate
                class="group relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">家系図一覧</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">既存の家系図を管理</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('settings.profile') }}" wire:navigate
                class="group relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">設定</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">アカウント設定を管理</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- 最近の家系図（実装予定） -->
        <div
            class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">最近の家系図</h2>
                <p class="text-gray-500 dark:text-gray-400">最近作成・編集した家系図がここに表示されます</p>
            </div>
        </div>
    </div>
</x-layouts.app>
