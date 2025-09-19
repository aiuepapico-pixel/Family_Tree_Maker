<?php

use App\Models\FamilyTree;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'familyTrees' => FamilyTree::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get(),
        ];
    }

    public function deleteFamilyTree($familyTreeId): void
    {
        $familyTree = FamilyTree::where('user_id', Auth::id())->findOrFail($familyTreeId);
        $familyTree->delete();

        session()->flash('success', '家系図を削除しました。');
    }
}; ?>

<div>
    <!-- ヘッダーセクション -->
    <div class="relative overflow-hidden bg-gradient-to-r from-gray-800 to-gray-600 py-12 mb-8">
        <!-- 背景パターン -->
        <div class="absolute inset-0 bg-gradient-to-br from-gray-600/60 to-gray-500/60"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-gray-800/85 to-gray-600/85"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-4xl font-bold text-white drop-shadow-lg mb-2">家系図一覧</h1>
                    <p class="text-gray-100 text-xl drop-shadow-md">作成した家系図を管理しましょう</p>
                </div>
                <a href="{{ route('family-trees.create') }}"
                    class="inline-flex items-center px-6 py-3 border border-transparent shadow-lg text-sm font-bold rounded-lg text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 hover:shadow-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    新規作成
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">

            @if ($familyTrees->isEmpty())
                <div
                    class="relative overflow-hidden rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 shadow-xl text-center py-16">
                    <!-- 背景パターン -->
                    <div class="absolute inset-0 bg-gradient-to-br from-gray-400/20 to-gray-500/20"></div>

                    <div class="relative z-10">
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">家系図がまだ作成されていません</h2>
                        <p class="text-gray-600 dark:text-gray-300 text-lg">「新規作成」から家系図を作成してください</p>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($familyTrees as $familyTree)
                        <div
                            class="group relative overflow-hidden rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105">
                            <!-- 背景パターン -->
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-gray-400/20 to-gray-500/20 group-hover:from-gray-400/30 group-hover:to-gray-500/30 transition-all duration-300">
                            </div>

                            <a href="{{ route('family-trees.show', $familyTree) }}" class="block relative z-10">
                                <div class="px-6 py-8">
                                    <h3
                                        class="text-xl font-bold text-gray-800 dark:text-gray-200 group-hover:text-gray-900 dark:group-hover:text-gray-100 transition-colors duration-300">
                                        {{ $familyTree->title }}
                                    </h3>
                                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-300 font-medium">
                                        {{ Str::limit($familyTree->description, 100) }}
                                    </p>
                                    <div class="mt-6">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                            @if ($familyTree->status === 'completed') bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                            @elseif($familyTree->status === 'active') bg-gray-300 text-gray-800 dark:bg-gray-600 dark:text-gray-200
                                            @else bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200 @endif">
                                            {{ match ($familyTree->status) {
                                                'completed' => '完了',
                                                'active' => '作成中',
                                                default => '下書き',
                                            } }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                            <div
                                class="relative z-10 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 px-6 py-4">
                                <div class="flex justify-between items-center">
                                    <div class="flex space-x-3">
                                        <a href="{{ route('family-trees.show', $familyTree) }}"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-bold rounded-lg text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 hover:shadow-lg">

                                            詳細表示
                                        </a>
                                        <a href="{{ route('family-trees.edit', $familyTree) }}"
                                            class="inline-flex items-center px-4 py-2 border-2 border-gray-300 dark:border-gray-600 text-sm font-bold rounded-lg text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 hover:shadow-lg">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            編集
                                        </a>
                                    </div>
                                    <button wire:click="deleteFamilyTree({{ $familyTree->id }})"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-bold rounded-lg text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 hover:shadow-lg"
                                        onclick="return confirm('本当に削除しますか？')">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        削除
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
