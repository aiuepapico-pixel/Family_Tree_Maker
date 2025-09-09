<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body
    class="min-h-screen bg-gradient-to-br from-sage-50 to-forest-100 antialiased dark:bg-gradient-to-br dark:from-forest-950 dark:to-sage-950">
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
        <!-- 背景の装飾的要素 -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div
                class="absolute top-1/4 left-1/4 w-64 h-64 bg-forest-200/20 dark:bg-forest-800/20 rounded-full blur-3xl">
            </div>
            <div
                class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-sage-200/15 dark:bg-sage-800/15 rounded-full blur-3xl">
            </div>
        </div>

        <div class="relative z-10 flex w-full max-w-md flex-col gap-6">
            <!-- ロゴとアプリ名 -->
            <div class="flex flex-col items-center gap-4 mb-4">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-3 font-medium group" wire:navigate>
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-forest-500 to-sage-600 shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                        <x-app-logo-icon class="size-8 fill-current text-white" />
                    </div>
                    <div class="text-center">
                        <h1 class="text-xl font-bold text-forest-800 dark:text-forest-200">
                            {{ config('app.name', 'Laravel') }}</h1>
                        <p class="text-sm text-sage-600 dark:text-sage-400">家系図作成システム</p>
                    </div>
                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>
            </div>

            <!-- カードコンテナ -->
            <div
                class="bg-white/80 dark:bg-forest-900/80 backdrop-blur-sm border border-sage-200 dark:border-sage-800 rounded-2xl shadow-xl p-8">
                {{ $slot }}
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
