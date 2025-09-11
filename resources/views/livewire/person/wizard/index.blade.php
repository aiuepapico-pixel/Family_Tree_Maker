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
        2 => ['title' => '生年月日', 'description' => '生年月日と現在の状況を入力してください'],
        3 => ['title' => '住所情報', 'description' => '現住所と本籍地を入力してください'],
        4 => ['title' => '続柄', 'description' => '被相続人との関係を入力してください'],
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
    public bool $is_alive = true;

    #[Rule('nullable|date|required_if:is_alive,false', message: '死亡日を入力してください')]
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

    // ステップ4: 続柄
    #[Rule('required|in:heir,deceased,renounced', message: '相続法上の地位を選択してください')]
    public string $legal_status = 'heir';

    #[Rule('nullable|string|max:100', message: '続柄は100文字以内で入力してください')]
    public ?string $relationship_to_deceased = null;

    public function mount(FamilyTree $familyTree): void
    {
        $this->familyTree = $familyTree;
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();

        if ($this->currentStep < count($this->steps)) {
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
                'death_date' => 'nullable|date|required_if:is_alive,false',
            ],
            3 => [
                'postal_code' => 'nullable|string|max:8',
                'current_address' => 'nullable|string|max:255',
                'registered_domicile' => 'nullable|string|max:255',
                'registered_address' => 'nullable|string|max:255',
            ],
            4 => [
                'legal_status' => 'required|in:heir,deceased,renounced',
                'relationship_to_deceased' => 'nullable|string|max:100',
            ],
            default => [],
        };

        $this->validate($rules);
    }

    public function save(): void
    {
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
            'relationship_to_deceased' => $this->relationship_to_deceased,
            'postal_code' => $this->postal_code,
            'current_address' => $this->current_address,
            'registered_domicile' => $this->registered_domicile,
            'registered_address' => $this->registered_address,
        ]);

        session()->flash('success', '人物情報を登録しました。');
        $this->redirect(route('family-trees.show', $this->familyTree), navigate: true);
    }
}; ?>

<div>
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- ヘッダー -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    人物情報の登録
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
                        <div class="space-y-6">
                            <div>
                                <label for="birth_date" class="block text-sm font-medium text-gray-700">生年月日</label>
                                <div class="mt-1">
                                    <input type="date" wire:model="birth_date" id="birth_date"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                @error('birth_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" wire:model="is_alive" id="is_alive"
                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_alive" class="font-medium text-gray-700">現在生存</label>
                                </div>
                            </div>

                            @if (!$is_alive)
                                <div>
                                    <label for="death_date"
                                        class="block text-sm font-medium text-gray-700">死亡年月日</label>
                                    <div class="mt-1">
                                        <input type="date" wire:model="death_date" id="death_date"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    @error('death_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- ステップ3: 住所情報 -->
                    @if ($currentStep === 3)
                        <div class="space-y-6">
                            <div>
                                <label for="postal_code" class="block text-sm font-medium text-gray-700">郵便番号</label>
                                <div class="mt-1">
                                    <input type="text" wire:model="postal_code" id="postal_code"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        placeholder="123-4567">
                                </div>
                                @error('postal_code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="current_address"
                                    class="block text-sm font-medium text-gray-700">現住所</label>
                                <div class="mt-1">
                                    <textarea wire:model="current_address" id="current_address" rows="3"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                </div>
                                @error('current_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="registered_domicile"
                                    class="block text-sm font-medium text-gray-700">本籍地</label>
                                <div class="mt-1">
                                    <textarea wire:model="registered_domicile" id="registered_domicile" rows="3"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                </div>
                                @error('registered_domicile')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="registered_address"
                                    class="block text-sm font-medium text-gray-700">登記記録上の住所</label>
                                <div class="mt-1">
                                    <textarea wire:model="registered_address" id="registered_address" rows="3"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                </div>
                                @error('registered_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endif

                    <!-- ステップ4: 続柄 -->
                    @if ($currentStep === 4)
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">相続法上の地位</label>
                                <div class="mt-4 space-y-4">
                                    <div class="flex items-center">
                                        <input type="radio" wire:model="legal_status" id="legal_status_heir"
                                            value="heir"
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="legal_status_heir"
                                            class="ml-3 block text-sm font-medium text-gray-700">
                                            相続人
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" wire:model="legal_status" id="legal_status_deceased"
                                            value="deceased"
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="legal_status_deceased"
                                            class="ml-3 block text-sm font-medium text-gray-700">
                                            被相続人
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" wire:model="legal_status" id="legal_status_renounced"
                                            value="renounced"
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="legal_status_renounced"
                                            class="ml-3 block text-sm font-medium text-gray-700">
                                            相続放棄
                                        </label>
                                    </div>
                                </div>
                                @error('legal_status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="relationship_to_deceased" class="block text-sm font-medium text-gray-700">
                                    被相続人との続柄
                                </label>
                                <div class="mt-1">
                                    <input type="text" wire:model="relationship_to_deceased"
                                        id="relationship_to_deceased"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        placeholder="例：長男、配偶者">
                                </div>
                                @error('relationship_to_deceased')
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
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        登録
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
