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
    Volt::route('family-trees', 'family-tree/index')->name('family-trees.index');
    Volt::route('family-trees/create', 'family-tree/create')->name('family-trees.create');
    Volt::route('family-trees/{familyTree}', 'family-tree/show')->name('family-trees.show');
    Volt::route('family-trees/{familyTree}/edit', 'family-tree/edit')->name('family-trees.edit');
    Volt::route('family-trees/{familyTree}/visual', 'family-tree/visual')->name('family-trees.visual');

    // 人物追加ウィザード
    // 被相続人専用ウィザード
    Volt::route("family-trees/{familyTree}/deceased-person/wizard", "deceased-person/wizard/index")
        ->name("deceased-person.wizard");
    Volt::route("family-trees/{familyTree}/deceased-person/edit", "deceased-person/edit")
        ->name("deceased-person.edit");
    Volt::route('family-trees/{familyTree}/persons/wizard', 'person/wizard/index')
        ->name('persons.wizard');

    // 関係性設定
    // 人物編集
    Volt::route("family-trees/{familyTree}/persons/{person}/edit", "person/edit")
        ->name("persons.edit");
    Volt::route('family-trees/{familyTree}/relationships/create', 'relationship/create')
        ->name('relationships.create');
    Volt::route('family-trees/{familyTree}/relationships/{relati
    onship}/edit', 'relationship/edit')
        ->name('relationships.edit');
});

// ログアウトルート
Route::post('logout', App\Livewire\Actions\Logout::class)
    ->middleware('auth')
    ->name('logout');

require __DIR__ . '/auth.php';
