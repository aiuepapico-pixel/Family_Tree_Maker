<?php

use App\Models\FamilyTree;
use App\Models\Person;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public FamilyTree $familyTree;
    public Person $deceasedPerson;

    #[Rule('required|string|max:255')]
    public string $family_name = '';

    #[Rule('required|string|max:255')]
    public string $given_name = '';

    #[Rule('required|string|max:255')]
    public string $family_name_kana = '';

    #[Rule('required|string|max:255')]
    public string $given_name_kana = '';

    #[Rule('required|in:male,female')]
    public string $gender = 'male';

    #[Rule('nullable|date|before:today')]
    public ?string $birth_date = null;

    #[Rule('nullable|date|after:birth_date')]
    public ?string $death_date = null;

    #[Rule('nullable|string|max:500')]
    public ?string $current_address = null;

    #[Rule('nullable|string|max:500')]
    public ?string $registered_domicile = null;

    #[Rule('nullable|string|max:500')]
    public ?string $registered_address = null;

    public function mount(FamilyTree $familyTree): void
    {
        $this->authorize('view', $familyTree);
        $this->familyTree = $familyTree;

        $this->deceasedPerson = $familyTree->people()->deceasedPerson()->first();

        if (!$this->deceasedPerson) {
            abort(404, '被相続人が見つかりません。');
        }

        // フォームに現在の値を設定
        $this->family_name = $this->deceasedPerson->family_name;
        $this->given_name = $this->deceasedPerson->given_name;
        $this->family_name_kana = $this->deceasedPerson->family_name_kana;
        $this->given_name_kana = $this->deceasedPerson->given_name_kana;
        $this->gender = $this->deceasedPerson->gender;
        $this->birth_date = $this->deceasedPerson->birth_date?->format('Y-m-d');
        $this->death_date = $this->deceasedPerson->death_date?->format('Y-m-d');
        $this->current_address = $this->deceasedPerson->current_address;
        $this->registered_domicile = $this->deceasedPerson->registered_domicile;
        $this->registered_address = $this->deceasedPerson->registered_address;
    }

    public function update(): void
    {
        $this->validate();

        $this->deceasedPerson->update([
            'family_name' => $this->family_name,
            'given_name' => $this->given_name,
            'family_name_kana' => $this->family_name_kana,
            'given_name_kana' => $this->given_name_kana,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date ? \Carbon\Carbon::parse($this->birth_date) : null,
            'death_date' => $this->death_date ? \Carbon\Carbon::parse($this->death_date) : null,
            'current_address' => $this->current_address,
            'registered_domicile' => $this->registered_domicile,
            'registered_address' => $this->registered_address,
        ]);

        session()->flash('success', '被相続人の情報を更新しました。');

        $this->redirect(route('family-trees.show', $this->familyTree));
    }
}; ?>

<div>
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <!-- ヘッダー部分 -->
            <div class="mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">被相続人情報の編集</h1>
                        <p class="mt-2 text-sm text-gray-500">{{ $familyTree->title }} - 被相続人</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('family-trees.show', $familyTree) }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            戻る
                        </a>
                    </div>
                </div>
            </div>

            <!-- 編集フォーム -->
            <div class="bg-white shadow rounded-lg">
                <form wire:submit="update" class="p-6 space-y-6">
                    <!-- 基本情報 -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">基本情報</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- 姓 -->
                            <div>
                                <label for="family_name" class="block text-sm font-medium text-gray-700">姓 <span
                                        class="text-red-500">*</span></label>
                                <input type="text" wire:model="family_name" id="family_name"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('family_name') border-red-300 @enderror">
                                @error('family_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 名 -->
                            <div>
                                <label for="given_name" class="block text-sm font-medium text-gray-700">名 <span
                                        class="text-red-500">*</span></label>
                                <input type="text" wire:model="given_name" id="given_name"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('given_name') border-red-300 @enderror">
                                @error('given_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 姓（カナ） -->
                            <div>
                                <label for="family_name_kana" class="block text-sm font-medium text-gray-700">姓（カナ）
                                    <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="family_name_kana" id="family_name_kana"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('family_name_kana') border-red-300 @enderror">
                                @error('family_name_kana')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 名（カナ） -->
                            <div>
                                <label for="given_name_kana" class="block text-sm font-medium text-gray-700">名（カナ） <span
                                        class="text-red-500">*</span></label>
                                <input type="text" wire:model="given_name_kana" id="given_name_kana"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('given_name_kana') border-red-300 @enderror">
                                @error('given_name_kana')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 性別 -->
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700">性別 <span
                                        class="text-red-500">*</span></label>
                                <select wire:model="gender" id="gender"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('gender') border-red-300 @enderror">
                                    <option value="male">男性</option>
                                    <option value="female">女性</option>
                                </select>
                                @error('gender')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 日付情報 -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">日付情報</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- 生年月日 -->
                            <div>
                                <label for="birth_date" class="block text-sm font-medium text-gray-700">生年月日</label>
                                <input type="date" wire:model="birth_date" id="birth_date"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('birth_date') border-red-300 @enderror">
                                @error('birth_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 死亡年月日 -->
                            <div>
                                <label for="death_date" class="block text-sm font-medium text-gray-700">死亡年月日</label>
                                <input type="date" wire:model="death_date" id="death_date"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('death_date') border-red-300 @enderror">
                                @error('death_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 住所情報 -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">住所情報</h3>
                        <div class="space-y-6">
                            <!-- 現住所 -->
                            <div>
                                <label for="current_address" class="block text-sm font-medium text-gray-700">現住所</label>
                                <textarea wire:model="current_address" id="current_address" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('current_address') border-red-300 @enderror"></textarea>
                                @error('current_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 本籍地 -->
                            <div>
                                <label for="registered_domicile"
                                    class="block text-sm font-medium text-gray-700">本籍地</label>
                                <textarea wire:model="registered_domicile" id="registered_domicile" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('registered_domicile') border-red-300 @enderror"></textarea>
                                @error('registered_domicile')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 住民票住所 -->
                            <div>
                                <label for="registered_address"
                                    class="block text-sm font-medium text-gray-700">住民票住所</label>
                                <textarea wire:model="registered_address" id="registered_address" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('registered_address') border-red-300 @enderror"></textarea>
                                @error('registered_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- ボタン -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('family-trees.show', $familyTree) }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            キャンセル
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            更新
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

