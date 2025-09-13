<?php

use App\Models\FamilyTree;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public FamilyTree $familyTree;
    public int $currentStep = 1;
    public array $steps = [
        1 => ['title' => '基本情報', 'description' => '氏名と性別を入力してください'],
        2 => ['title' => '生年月日', 'description' => '生年月日と死亡日を入力してください'],
        3 => ['title' => '住所情報', 'description' => '現住所と本籍地を入力してください'],
        4 => ['title' => '相続情報', 'description' => '相続に関する情報を入力してください'],
    ];

    // ステップ1: 基本情報
    #[Rule('required|string|max:100', message: '姓を入力してください')]
    public string $family_name = '';

    #[Rule('required|string|max:100', message: '名を入力してください')]
    public string $given_name = '';

    #[Rule('nullable|string|max:100', message: '姓（ひらがな）は100文字以内で入力してください')]
    public string $family_name_kana = '';

    #[Rule('nullable|string|max:100', message: '名（ひらがな）は100文字以内で入力してください')]
    public string $given_name_kana = '';

    #[Rule('required|in:male,female,unknown', message: '性別を選択してください')]
    public string $gender = 'unknown';

    // ステップ2: 生年月日
    #[Rule('nullable|date', message: '有効な日付を入力してください')]
    public ?string $birth_date = null;

    #[Rule('required|boolean')]
    public bool $is_alive = false; // 被相続人は死亡している

    #[Rule('required|date', message: '死亡日を入力してください')]
    public ?string $death_date = null;

    // ステップ3: 住所情報
    #[Rule('nullable|string|max:8', message: '郵便番号は8文字以内で入力してください')]
    public ?string $postal_code = null;

    #[Rule('nullable|string|max:255', message: '現住所は255文字以内で入力してください')]
    public ?string $current_address = null;

    #[Rule('nullable|string|max:255', message: '本籍地は255文字以内で入力してください')]
    public ?string $registered_domicile = null;

    #[Rule('nullable|string|max:255', message: '登記記録上の住所は255文字以内で入力してください')]
    public ?string $registered_address = null;

    // ステップ4: 相続情報
    #[Rule('required|in:deceased', message: '法的地位を選択してください')]
    public string $legal_status = 'deceased';

    #[Rule('nullable|string|max:1000', message: '備考は1000文字以内で入力してください')]
    public ?string $notes = null;

    public function mount(FamilyTree $familyTree): void
    {
        $this->authorize('update', $familyTree);
        $this->familyTree = $familyTree;
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        if ($this->currentStep < 4) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    private function validateCurrentStep(): void
    {
        $rules = match ($this->currentStep) {
            1 => [
                'family_name' => 'required|string|max:100',
                'given_name' => 'required|string|max:100',
                'family_name_kana' => 'nullable|string|max:100',
                'given_name_kana' => 'nullable|string|max:100',
                'gender' => 'required|in:male,female,unknown',
            ],
            2 => [
                'birth_date' => 'nullable|date',
                'is_alive' => 'required|boolean',
                'death_date' => 'required|date',
            ],
            3 => [
                'postal_code' => 'nullable|string|max:8',
                'current_address' => 'nullable|string|max:255',
                'registered_domicile' => 'nullable|string|max:255',
                'registered_address' => 'nullable|string|max:255',
            ],
            4 => [
                'legal_status' => 'required|in:deceased',
                'notes' => 'nullable|string|max:1000',
            ],
        };

        $this->validate($rules);
    }

    public function save(): void
    {
        // 最後のステップでない場合は何もしない
        if ($this->currentStep !== 4) {
            return;
        }

        $this->validateCurrentStep();

        $person = $this->familyTree->people()->create([
            'family_name' => $this->family_name,
            'given_name' => $this->given_name,
            'family_name_kana' => $this->family_name_kana,
            'given_name_kana' => $this->given_name_kana,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date,
            'death_date' => $this->death_date,
            'is_alive' => $this->is_alive,
            'legal_status' => $this->legal_status,
            'postal_code' => $this->postal_code,
            'current_address' => $this->current_address,
            'registered_domicile' => $this->registered_domicile,
            'registered_address' => $this->registered_address,
            'notes' => $this->notes,
        ]);

        // 被相続人として設定
        $this->familyTree->update([
            'deceased_person_id' => $person->id,
            'status' => 'active', // 被相続人登録後は作成中に変更
        ]);

        session()->flash('success', '被相続人を登録しました。次に家族構成員を追加してください。');
        $this->redirect(route('family-trees.show', $this->familyTree), navigate: true);
    }
}; ?>

<div>
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- ヘッダー -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    被相続人情報の登録
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $familyTree->title }}
                </p>
            </div>
        </div>

        <!-- 進捗バー -->
        <div class="mb-8">
            <nav aria-label="Progress">
                <ol role="list"
                    class="border border-gray-300 rounded-md divide-y divide-gray-300 md:flex md:divide-y-0">
                    @foreach ($steps as $index => $step)
                        <li class="relative md:flex-1 md:flex">
                            <a href="#" class="group flex items-center w-full">
                                <span class="px-6 py-4 flex items-center text-sm font-medium">
                                    <span
                                        class="flex-shrink-0 w-10 h-10 flex items-center justify-center
                                        @if ($index < $currentStep) bg-blue-600 rounded-full
                                        @elseif($index === $currentStep)
                                            border-2 border-blue-600 rounded-full
                                        @else
                                            border-2 border-gray-300 rounded-full @endif">
                                        @if ($index < $currentStep)
                                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        @else
                                            <span
                                                class="{{ $index === $currentStep ? 'text-blue-600' : 'text-gray-500' }}">
                                                {{ $index }}
                                            </span>
                                        @endif
                                    </span>
                                    <span
                                        class="ml-4 text-sm font-medium
                                        @if ($index < $currentStep) text-gray-900
                                        @elseif($index === $currentStep)
                                            text-blue-600
                                        @else
                                            text-gray-500 @endif">
                                        {{ $step['title'] }}
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>

        <!-- 現在のステップの説明 -->
        <div class="mb-6">
            <p class="text-sm text-gray-500">
                {{ $steps[$currentStep]['description'] }}
            </p>
        </div>

        <!-- フォーム -->
        <form wire:submit="save" class="space-y-8">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <!-- ステップ1: 基本情報 -->
                    @if ($currentStep === 1)
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <label for="family_name" class="block text-sm font-medium text-gray-700">姓</label>
                                <div class="mt-1">
                                    <input type="text" wire:model="family_name" id="family_name"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                @error('family_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-1">
                                <label for="given_name" class="block text-sm font-medium text-gray-700">名</label>
                                <div class="mt-1">
                                    <input type="text" wire:model="given_name" id="given_name"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                @error('given_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-1">
                                <label for="family_name_kana"
                                    class="block text-sm font-medium text-gray-700">姓（ひらがな）</label>
                                <div class="mt-1">
                                    <input type="text" wire:model="family_name_kana" id="family_name_kana"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                @error('family_name_kana')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-1">
                                <label for="given_name_kana"
                                    class="block text-sm font-medium text-gray-700">名（ひらがな）</label>
                                <div class="mt-1">
                                    <input type="text" wire:model="given_name_kana" id="given_name_kana"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                @error('given_name_kana')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">性別</label>
                                <div class="mt-4 space-y-4">
                                    <div class="flex items-center">
                                        <input type="radio" wire:model="gender" id="gender_male" value="male"
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="gender_male" class="ml-3 block text-sm font-medium text-gray-700">
                                            男性
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" wire:model="gender" id="gender_female" value="female"
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="gender_female" class="ml-3 block text-sm font-medium text-gray-700">
                                            女性
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" wire:model="gender" id="gender_unknown" value="unknown"
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="gender_unknown"
                                            class="ml-3 block text-sm font-medium text-gray-700">
                                            不明
                                        </label>
                                    </div>
                                </div>
                                @error('gender')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endif

                    <!-- ステップ2: 生年月日 -->
                    @if ($currentStep === 2)
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <label for="birth_date" class="block text-sm font-medium text-gray-700">生年月日</label>
                                <div class="mt-1">
                                    <input type="date" wire:model="birth_date" id="birth_date"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                @error('birth_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-1">
                                <label for="death_date" class="block text-sm font-medium text-gray-700">死亡日 <span
                                        class="text-red-500">*</span></label>
                                <div class="mt-1">
                                    <input type="date" wire:model="death_date" id="death_date"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                @error('death_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800">被相続人について</h3>
                                            <div class="mt-2 text-sm text-red-700">
                                                <p>被相続人は故人（死亡者）です。死亡日は必須項目です。</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- ステップ3: 住所情報 -->
                    @if ($currentStep === 3)
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <label for="postal_code" class="block text-sm font-medium text-gray-700">郵便番号</label>
                                <div class="mt-1">
                                    <input type="text" wire:model="postal_code" id="postal_code"
                                        placeholder="123-4567"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                @error('postal_code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-1">
                                <label for="current_address"
                                    class="block text-sm font-medium text-gray-700">現住所</label>
                                <div class="mt-1">
                                    <input type="text" wire:model="current_address" id="current_address"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                @error('current_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-1">
                                <label for="registered_domicile"
                                    class="block text-sm font-medium text-gray-700">本籍地</label>
                                <div class="mt-1">
                                    <input type="text" wire:model="registered_domicile" id="registered_domicile"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                @error('registered_domicile')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-1">
                                <label for="registered_address"
                                    class="block text-sm font-medium text-gray-700">登記記録上の住所</label>
                                <div class="mt-1">
                                    <input type="text" wire:model="registered_address" id="registered_address"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                @error('registered_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endif

                    <!-- ステップ4: 相続情報 -->
                    @if ($currentStep === 4)
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">法的地位</label>
                                <div class="mt-2">
                                    <div class="flex items-center">
                                        <input type="radio" wire:model="legal_status" id="legal_status_deceased"
                                            value="deceased"
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300" checked>
                                        <label for="legal_status_deceased"
                                            class="ml-3 block text-sm font-medium text-gray-700">
                                            被相続人（故人）
                                        </label>
                                    </div>
                                </div>
                                @error('legal_status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">備考</label>
                                <div class="mt-1">
                                    <textarea wire:model="notes" id="notes" rows="4"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        placeholder="相続に関する特記事項があれば記入してください"></textarea>
                                </div>
                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ナビゲーションボタン -->
            <div class="flex justify-between">
                <button type="button" wire:click="previousStep"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    @if ($currentStep === 1) disabled @endif>
                    前へ
                </button>

                @if ($currentStep < count($steps))
                    <button type="button" wire:click="nextStep"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        次へ
                    </button>
                @else
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        被相続人を登録
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
