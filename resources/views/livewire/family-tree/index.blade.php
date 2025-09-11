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
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">家系図一覧</h1>
                <a href="{{ route('family-trees.create') }}"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    新規作成
                </a>
            </div>

            @if ($familyTrees->isEmpty())
                <div class="text-center py-12">
                    <p class="text-gray-500">家系図がまだ作成されていません。</p>
                    <p class="text-gray-500">「新規作成」から家系図を作成してください。</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($familyTrees as $familyTree)
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ $familyTree->title }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ Str::limit($familyTree->description, 100) }}
                                </p>
                                <div class="mt-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if ($familyTree->status === 'completed') bg-green-100 text-green-800
                                        @elseif($familyTree->status === 'active') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ match ($familyTree->status) {
                                            'completed' => '完了',
                                            'active' => '作成中',
                                            default => '下書き',
                                        } }}
                                    </span>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-4 sm:px-6">
                                <div class="flex justify-end space-x-3">
                                    <a href="{{ route('family-trees.edit', $familyTree) }}"
                                        class="text-blue-600 hover:text-blue-900">編集</a>
                                    <a href="{{ route('family-trees.show', $familyTree) }}"
                                        class="text-green-600 hover:text-green-900">表示</a>
                                    <button wire:click="deleteFamilyTree({{ $familyTree->id }})"
                                        class="text-red-600 hover:text-red-900" onclick="return confirm('本当に削除しますか？')">
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
