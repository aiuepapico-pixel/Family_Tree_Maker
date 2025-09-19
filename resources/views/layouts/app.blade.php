<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', '相続関係説明図作成アプリ') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Livewire Styles -->
    @livewireStyles

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireScripts
</head>

<body class="h-full font-sans antialiased bg-gray-50 dark:bg-gray-900">
    <div class="flex h-screen">
        <!-- サイドバー -->
        <div class="hidden md:flex md:w-64 md:flex-col">
            <div
                class="flex flex-col flex-grow pt-5 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
                <!-- ロゴ -->
                <div class="flex items-center flex-shrink-0 px-4">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-app-logo class="block h-8 w-auto" />
                    </a>
                </div>

                <!-- ナビゲーションメニュー -->
                <div class="mt-8 flex-grow flex flex-col">
                    <nav class="flex-1 px-2 space-y-1">
                        <!-- ダッシュボード -->
                        <a href="{{ route('dashboard') }}" wire:navigate @class([
                            'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                            'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' => request()->routeIs(
                                'dashboard'),
                            'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' => !request()->routeIs(
                                'dashboard'),
                        ])>
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                            </svg>
                            ダッシュボード
                        </a>

                        <!-- 家系図 -->
                        <a href="{{ route('family-trees.index') }}" wire:navigate @class([
                            'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                            'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' => request()->routeIs(
                                'family-trees.*'),
                            'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' => !request()->routeIs(
                                'family-trees.*'),
                        ])>
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            家系図
                        </a>

                        <!-- 新しい家系図作成 -->
                        <a href="{{ route('family-trees.create') }}" wire:navigate
                            class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors duration-200">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            新しい家系図
                        </a>
                    </nav>

                    <!-- 設定セクション -->
                    <div class="mt-8">
                        <div class="px-2">
                            <h3
                                class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                設定</h3>
                        </div>
                        <nav class="mt-2 px-2 space-y-1">
                            <a href="{{ route('settings.profile') }}" wire:navigate @class([
                                'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                                'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' => request()->routeIs(
                                    'settings.profile'),
                                'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' => !request()->routeIs(
                                    'settings.profile'),
                            ])>
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                プロフィール
                            </a>
                            <a href="{{ route('settings.password') }}" wire:navigate @class([
                                'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                                'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' => request()->routeIs(
                                    'settings.password'),
                                'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' => !request()->routeIs(
                                    'settings.password'),
                            ])>
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                                パスワード
                            </a>
                            <a href="{{ route('settings.appearance') }}" wire:navigate @class([
                                'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                                'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' => request()->routeIs(
                                    'settings.appearance'),
                                'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' => !request()->routeIs(
                                    'settings.appearance'),
                            ])>
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z">
                                    </path>
                                </svg>
                                外観
                            </a>
                        </nav>
                    </div>

                    <!-- ユーザー情報 -->
                    <div class="flex-shrink-0 flex border-t border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex-shrink-0 w-full group block">
                            <div class="flex items-center">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ Auth::user()->name }}</p>
                                    <p
                                        class="text-xs font-medium text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                                        {{ Auth::user()->email }}</p>
                                </div>
                            </div>
                            <div class="mt-2">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                        ログアウト
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- メインコンテンツ -->
        <div class="flex flex-col w-0 flex-1 overflow-hidden">
            <!-- モバイル用ヘッダー -->
            <div class="md:hidden pl-1 pt-1 sm:pl-3 sm:pt-3">
                <button type="button" @click="$store.mobileMenuOpen = !$store.mobileMenuOpen"
                    class="-ml-0.5 -mt-0.5 h-12 w-12 inline-flex items-center justify-center rounded-md text-gray-500 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gray-500">
                    <span class="sr-only">メニューを開く</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>

            <!-- モバイルメニュー -->
            <div x-data x-show="$store.mobileMenuOpen" class="md:hidden" style="display: none;">
                <div
                    class="pt-2 pb-3 space-y-1 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <a href="{{ route('dashboard') }}" wire:navigate @class([
                        'block pl-3 pr-4 py-2 border-l-4 text-base font-medium',
                        'border-gray-500 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-white' => request()->routeIs(
                            'dashboard'),
                        'border-transparent text-gray-600 dark:text-gray-300 hover:border-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-800 dark:hover:text-white' => !request()->routeIs(
                            'dashboard'),
                    ])>
                        ダッシュボード
                    </a>
                    <a href="{{ route('family-trees.index') }}" wire:navigate @class([
                        'block pl-3 pr-4 py-2 border-l-4 text-base font-medium',
                        'border-gray-500 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-white' => request()->routeIs(
                            'family-trees.*'),
                        'border-transparent text-gray-600 dark:text-gray-300 hover:border-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-800 dark:hover:text-white' => !request()->routeIs(
                            'family-trees.*'),
                    ])>
                        家系図
                    </a>
                    <a href="{{ route('family-trees.create') }}" wire:navigate
                        class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 dark:text-gray-300 hover:border-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-800 dark:hover:text-white">
                        新しい家系図
                    </a>
                </div>
                <div class="pt-4 pb-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center px-4">
                        <div class="flex-shrink-0">
                            <div
                                class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800 dark:text-white">{{ Auth::user()->name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ Auth::user()->email }}</div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <a href="{{ route('settings.profile') }}" wire:navigate
                            class="block px-4 py-2 text-base font-medium text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                            プロフィール
                        </a>
                        <a href="{{ route('settings.password') }}" wire:navigate
                            class="block px-4 py-2 text-base font-medium text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                            パスワード
                        </a>
                        <a href="{{ route('settings.appearance') }}" wire:navigate
                            class="block px-4 py-2 text-base font-medium text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                            外観
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="block w-full px-4 py-2 text-left text-base font-medium text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                                ログアウト
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- メインコンテンツエリア -->
            <main class="flex-1 relative overflow-y-auto focus:outline-none">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
