<?php

use App\Models\FamilyTree;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    #[Rule('required|string|max:255')]
    public string $title = '';

    #[Rule('nullable|string|max:1000')]
    public string $description = '';

    public function save(): void
    {
        $validated = $this->validate();

        $familyTree = new FamilyTree([...$validated, 'user_id' => Auth::id(), 'status' => 'draft']);

        $familyTree->save();

        session()->flash('success', '家系図を作成して被相続人を登録しました。被相続人を登録してください。');
        $this->redirect(route('deceased-person.wizard', $familyTree), navigate: true);
    }
}; ?>

<div>
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">新規家系図作成して被相続人を登録</h1>
            </div>

            <form wire:submit="save" class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">
                        タイトル
                    </label>
                    <div class="mt-1">
                        <input type="text" wire:model="title" id="title"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            placeholder="例：山田家家系図">
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
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            placeholder="家系図の説明や備考を入力してください"></textarea>
                    </div>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="history.back()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        戻る
                    </button>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        作成して被相続人を登録
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
