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

<body class="h-full font-sans antialiased">
    <div class="min-h-full">
        <!-- ナビゲーションバー -->
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- ロゴ -->
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}" wire:navigate>
                                <x-app-logo class="block h-8 w-auto" />
                            </a>
                        </div>

                        <!-- ナビゲーションリンク -->
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('dashboard') }}" wire:navigate @class([
                                'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                                'border-blue-500 text-gray-900' => request()->routeIs('dashboard'),
                                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' => !request()->routeIs(
                                    'dashboard'),
                            ])>
                                ダッシュボード
                            </a>
                            <a href="{{ route('family-trees.index') }}" wire:navigate @class([
                                'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                                'border-blue-500 text-gray-900' => request()->routeIs('family-trees.*'),
                                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' => !request()->routeIs(
                                    'family-trees.*'),
                            ])>
                                家系図
                            </a>
                            <!-- 設定メニューをドロップダウンに変更 -->
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button type="button" @click="open = !open" @class([
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                                    'border-blue-500 text-gray-900' => request()->routeIs('settings.*'),
                                    'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' => !request()->routeIs(
                                        'settings.*'),
                                ])>
                                    設定
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                                <div x-show="open" x-transition
                                    class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                    style="display: none;">
                                    <a href="{{ route('settings.profile') }}" wire:navigate
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">プロフィール</a>
                                    <a href="{{ route('settings.password') }}" wire:navigate
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">パスワード</a>
                                    <a href="{{ route('settings.appearance') }}" wire:navigate
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">外観</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ユーザーメニュー -->
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button type="button" @click="open = !open"
                                class="flex rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <span class="sr-only">メニューを開く</span>
                                <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-700">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </span>
                                </div>
                            </button>

                            <div x-show="open" x-transition
                                class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                style="display: none;">
                                <div class="px-4 py-2 text-sm text-gray-500 border-b border-gray-200">
                                    {{ Auth::user()->name }}
                                </div>
                                <a href="{{ route('family-trees.index') }}" wire:navigate
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    家系図一覧
                                </a>
                                <a href="{{ route('family-trees.create') }}" wire:navigate
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    新しい家系図
                                </a>
                                <div class="border-t border-gray-200 my-1"></div>
                                <a href="{{ route('settings.profile') }}" wire:navigate
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    設定
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                                        ログアウト
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- モバイルメニューボタン -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button type="button" @click="$store.mobileMenuOpen = !$store.mobileMenuOpen"
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                            <span class="sr-only">メニューを開く</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- モバイルメニュー -->
            <div x-data x-show="$store.mobileMenuOpen" class="sm:hidden" style="display: none;">
                <div class="space-y-1 pb-3 pt-2">
                    <a href="{{ route('dashboard') }}" wire:navigate @class([
                        'block border-l-4 py-2 pl-3 pr-4 text-base font-medium',
                        'border-blue-500 bg-blue-50 text-blue-700' => request()->routeIs(
                            'dashboard'),
                        'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800' => !request()->routeIs(
                            'dashboard'),
                    ])>
                        ダッシュボード
                    </a>
                    <a href="{{ route('family-trees.index') }}" wire:navigate @class([
                        'block border-l-4 py-2 pl-3 pr-4 text-base font-medium',
                        'border-blue-500 bg-blue-50 text-blue-700' => request()->routeIs(
                            'family-trees.*'),
                        'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800' => !request()->routeIs(
                            'family-trees.*'),
                    ])>
                        家系図
                    </a>
                </div>

                <div class="border-t border-gray-200 pb-3 pt-4">
                    <div class="flex items-center px-4">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                <span class="text-sm font-medium text-gray-700">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                            <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <a href="{{ route('family-trees.index') }}" wire:navigate
                            class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                            家系図一覧
                        </a>
                        <a href="{{ route('family-trees.create') }}" wire:navigate
                            class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                            新しい家系図
                        </a>
                        <a href="{{ route('settings.profile') }}" wire:navigate
                            class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                            設定
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="block w-full px-4 py-2 text-left text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                                ログアウト
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- メインコンテンツ -->
        <main>
            {{ $slot }}
        </main>
    </div>
</body>

</html>
