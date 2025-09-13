<?php

use App\Models\FamilyTree;
use App\Models\Person;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    public FamilyTree $familyTree;

    #[Rule('required|string|max:255')]
    public string $title = '';

    #[Rule('nullable|string|max:1000')]
    public string $description = '';

    #[Rule('nullable|exists:people,id')]
    public ?int $deceased_person_id = null;

    #[Rule('nullable|date')]
    public ?string $inheritance_date = null;

    public function mount(FamilyTree $familyTree): void
    {
        $this->authorize('update', $familyTree);
        $this->familyTree = $familyTree;
        $this->title = $familyTree->title;
        $this->description = $familyTree->description ?? '';
        $this->deceased_person_id = $familyTree->deceased_person_id;
        $this->inheritance_date = $familyTree->inheritance_date?->format('Y-m-d');
    }

    public function with(): array
    {
        return [
            'people' => $this->familyTree->people()->orderBy('family_name')->orderBy('given_name')->get(),
        ];
    }

    public function save(): void
    {
        $this->authorize('update', $this->familyTree);

        $validated = $this->validate();

        $this->familyTree->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'deceased_person_id' => $validated['deceased_person_id'],
            'inheritance_date' => $validated['inheritance_date'],
        ]);

        session()->flash('success', '家系図情報を更新しました。');
        $this->redirect(route('persons.wizard', $this->familyTree), navigate: true);
    }

    public function updateStatus(string $status): void
    {
        $this->authorize('update', $this->familyTree);

        if (!in_array($status, ['draft', 'active', 'completed'])) {
            return;
        }

        $this->familyTree->update(['status' => $status]);
        session()->flash('success', '家系図情報を更新しました。');
        $this->redirect(route('persons.wizard', $this->familyTree), navigate: true);
    }
}; ?>

<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6 flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">家系図編集</h1>
                <div class="flex space-x-3">
                    <button wire:click="updateStatus('draft')" @class([
                        'px-4 py-2 text-sm font-medium rounded-md',
                        'bg-gray-100 text-gray-800' => $familyTree->status === 'draft',
                        'text-gray-600 hover:bg-gray-50' => $familyTree->status !== 'draft',
                    ])>
                        下書き
                    </button>
                    <button wire:click="updateStatus('active')" @class([
                        'px-4 py-2 text-sm font-medium rounded-md',
                        'bg-blue-100 text-blue-800' => $familyTree->status === 'active',
                        'text-gray-600 hover:bg-gray-50' => $familyTree->status !== 'active',
                    ])>
                        作成中
                    </button>
                    <button wire:click="updateStatus('completed')" @class([
                        'px-4 py-2 text-sm font-medium rounded-md',
                        'bg-green-100 text-green-800' => $familyTree->status === 'completed',
                        'text-gray-600 hover:bg-gray-50' => $familyTree->status !== 'completed',
                    ])>
                        完了
                    </button>
                </div>
            </div>

            <form wire:submit="save" class="space-y-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">
                                タイトル
                            </label>
                            <div class="mt-1">
                                <input type="text" wire:model="title" id="title"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                説明
                            </label>
                            <div class="mt-1">
                                <textarea wire:model="description" id="description" rows="3"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                            </div>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="deceased_person_id" class="block text-sm font-medium text-gray-700">
                                被相続人
                            </label>
                            <div class="mt-1">
                                <select wire:model="deceased_person_id" id="deceased_person_id"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">選択してください</option>
                                    @foreach ($people as $person)
                                        <option value="{{ $person->id }}">
                                            {{ $person->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('deceased_person_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="inheritance_date" class="block text-sm font-medium text-gray-700">
                                相続開始日
                            </label>
                            <div class="mt-1">
                                <input type="date" wire:model="inheritance_date" id="inheritance_date"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('inheritance_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('family-trees.show', $familyTree) }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        キャンセル
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        保存して構成員を追加
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
