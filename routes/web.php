<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // 家系図関連のルート
    Volt::route('family-trees', 'family-tree.index')->name('family-trees.index');
    Volt::route('family-trees/create', 'family-tree.create')->name('family-trees.create');
    Volt::route('family-trees/{familyTree}', 'family-tree.show')->name('family-trees.show');
    Volt::route('family-trees/{familyTree}/edit', 'family-tree.edit')->name('family-trees.edit');
});

require __DIR__ . '/auth.php';
