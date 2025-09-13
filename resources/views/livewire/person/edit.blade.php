<?php

use App\Models\FamilyTree;
use App\Models\Person;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public FamilyTree $familyTree;
    public Person $person;

    // 続柄の階層構造（wizard/index.blade.phpと同じ構造）
    public array $relationshipHierarchy = [
        'spouse' => [
            'label' => '配偶者',
            'options' => [
                'wife' => '妻',
                'husband' => '夫',
            ],
        ],
        'child' => [
            'label' => '子',
            'options' => [
                'eldest_son' => '長男',
                'eldest_daughter' => '長女',
                'adopted_child' => '養子',
            ],
        ],
        'parent' => [
            'label' => '父母',
            'options' => [
                'father' => '父',
                'mother' => '母',
            ],
        ],
        'grandparent' => [
            'label' => '祖父母',
            'options' => [
                'grandfather' => '祖父',
                'grandmother' => '祖母',
            ],
        ],
        'sibling' => [
            'label' => '兄弟姉妹',
            'options' => [
                'elder_brother' => '兄',
                'younger_brother' => '弟',
                'elder_sister' => '姉',
                'younger_sister' => '妹',
            ],
        ],
        'other' => [
            'label' => 'その他',
            'options' => [],
        ],
    ];

    // 現在の選択パス
    public array $selectedPath = [];
    public string $finalRelationship = '';

    // 基本情報
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

    // 生年月日
    #[Rule('nullable|date', message: '有効な日付を入力してください')]
    public ?string $birth_date = null;

    #[Rule('required|boolean')]
    public bool $is_alive = true;

    #[Rule('nullable|date|required_if:is_alive,false', message: '死亡日を入力してください')]
    public ?string $death_date = null;

    // 住所情報
    #[Rule('nullable|string|max:8', message: '郵便番号は8文字以内で入力してください')]
    public ?string $postal_code = null;

    #[Rule('nullable|string|max:255', message: '現住所は255文字以内で入力してください')]
    public ?string $current_address = null;

    #[Rule('nullable|string|max:255', message: '本籍地は255文字以内で入力してください')]
    public ?string $registered_domicile = null;

    #[Rule('nullable|string|max:255', message: '住民票住所は255文字以内で入力してください')]
    public ?string $registered_address = null;

    // 相続法上の地位
    #[Rule('required|in:heir,renounced', message: '相続法上の地位を選択してください')]
    public string $legal_status = 'heir';

    // 続柄関連
    #[Rule('nullable|string|max:100', message: 'その他の続柄は100文字以内で入力してください')]
    public ?string $custom_relationship = null;

    // 最終的な続柄（データベース保存用）
    public ?string $relationship_to_deceased = null;

    public function mount(FamilyTree $familyTree, Person $person): void
    {
        $this->authorize('update', $familyTree);
        $this->authorize('update', $person);

        $this->familyTree = $familyTree;
        $this->person = $person;

        // 既存データをフォームに設定
        $this->family_name = $person->family_name;
        $this->given_name = $person->given_name;
        $this->family_name_kana = $person->family_name_kana ?? '';
        $this->given_name_kana = $person->given_name_kana ?? '';
        $this->gender = $person->gender;
        $this->birth_date = $person->birth_date?->format('Y-m-d');
        $this->is_alive = $person->is_alive;
        $this->death_date = $person->death_date?->format('Y-m-d');
        $this->postal_code = $person->postal_code;
        $this->current_address = $person->current_address;
        $this->registered_domicile = $person->registered_domicile;
        $this->registered_address = $person->registered_address;
        $this->legal_status = $person->legal_status;
        $this->relationship_to_deceased = $person->relationship_to_deceased;

        // 続柄の選択パスを初期化
        $this->initializeRelationshipPath();
    }

    // 既存の続柄データを取得
    public function getExistingRelationships(): array
    {
        return $this->familyTree
            ->people()
            ->whereNotNull('relationship_to_deceased')
            ->where('id', '!=', $this->person->id) // 現在編集中の人物は除外
            ->pluck('relationship_to_deceased')
            ->toArray();
    }

    // 動的に続柄選択肢を生成
    public function generateDynamicChildOptions(): array
    {
        $existingRelationships = $this->getExistingRelationships();
        $options = [];

        // 既存の続柄を分析
        $sonCount = 0;
        $daughterCount = 0;
        $adoptedCount = 0;

        foreach ($existingRelationships as $relationship) {
            if (str_contains($relationship, '長男') || str_contains($relationship, '二男') || str_contains($relationship, '三男') || str_contains($relationship, '四男') || str_contains($relationship, '五男')) {
                $sonCount++;
            } elseif (str_contains($relationship, '長女') || str_contains($relationship, '二女') || str_contains($relationship, '三女') || str_contains($relationship, '四女') || str_contains($relationship, '五女')) {
                $daughterCount++;
            } elseif (str_contains($relationship, '養子')) {
                $adoptedCount++;
            }
        }

        // 長男・長女が選択されていない場合は表示
        if (!in_array('長男', $existingRelationships)) {
            $options['eldest_son'] = '長男';
        }
        if (!in_array('長女', $existingRelationships)) {
            $options['eldest_daughter'] = '長女';
        }

        // 二男・二女の選択肢を生成
        if ($sonCount >= 1 && !in_array('二男', $existingRelationships)) {
            $options['second_son'] = '二男';
        }
        if ($daughterCount >= 1 && !in_array('二女', $existingRelationships)) {
            $options['second_daughter'] = '二女';
        }

        // 三男・三女の選択肢を生成
        if ($sonCount >= 2 && !in_array('三男', $existingRelationships)) {
            $options['third_son'] = '三男';
        }
        if ($daughterCount >= 2 && !in_array('三女', $existingRelationships)) {
            $options['third_daughter'] = '三女';
        }

        // 四男・四女の選択肢を生成
        if ($sonCount >= 3 && !in_array('四男', $existingRelationships)) {
            $options['fourth_son'] = '四男';
        }
        if ($daughterCount >= 3 && !in_array('四女', $existingRelationships)) {
            $options['fourth_daughter'] = '四女';
        }

        // 五男・五女の選択肢を生成
        if ($sonCount >= 4 && !in_array('五男', $existingRelationships)) {
            $options['fifth_son'] = '五男';
        }
        if ($daughterCount >= 4 && !in_array('五女', $existingRelationships)) {
            $options['fifth_daughter'] = '五女';
        }

        // 養子の選択肢
        if ($adoptedCount === 0) {
            $options['adopted_child'] = '養子';
        }

        return $options;
    }

    // 既存の続柄から選択パスを初期化
    private function initializeRelationshipPath(): void
    {
        if (!$this->relationship_to_deceased) {
            return;
        }

        $relationship = $this->relationship_to_deceased;

        // 動的に生成された子の続柄をチェック
        $dynamicChildOptions = $this->generateDynamicChildOptions();
        if (in_array($relationship, $dynamicChildOptions)) {
            $this->selectedPath = ['child', array_search($relationship, $dynamicChildOptions)];
            $this->finalRelationship = $relationship;
            return;
        }

        // 階層構造から検索
        foreach ($this->relationshipHierarchy as $category => $data) {
            if (isset($data['options'])) {
                foreach ($data['options'] as $key => $label) {
                    if ($label === $relationship) {
                        $this->selectedPath = [$category, $key];
                        $this->finalRelationship = $relationship;
                        return;
                    }
                }
            }
        }

        // その他の場合
        if ($relationship !== 'その他') {
            $this->custom_relationship = $relationship;
            $this->selectedPath = ['other'];
            $this->finalRelationship = 'その他';
        }
    }

    public function selectRelationship($key): void
    {
        $this->selectedPath[] = $key;
        $this->updateFinalRelationship();
    }

    public function goBackRelationship(): void
    {
        if (count($this->selectedPath) > 0) {
            array_pop($this->selectedPath);
            $this->updateFinalRelationship();
        }
    }

    private function updateFinalRelationship(): void
    {
        if (empty($this->selectedPath)) {
            $this->finalRelationship = '';
            $this->relationship_to_deceased = null;
            return;
        }

        // 子が選択された場合の動的続柄処理
        if ($this->selectedPath[0] === 'child' && count($this->selectedPath) > 1) {
            $childKey = $this->selectedPath[1];
            $dynamicOptions = $this->generateDynamicChildOptions();

            if (isset($dynamicOptions[$childKey])) {
                $this->finalRelationship = $dynamicOptions[$childKey];
                $this->relationship_to_deceased = $this->finalRelationship;
                return;
            }
        }

        $path = $this->selectedPath;
        $current = $this->relationshipHierarchy;

        foreach ($path as $key) {
            if (isset($current[$key])) {
                $current = $current[$key];
            } else {
                break;
            }
        }

        $this->finalRelationship = $current['label'] ?? '';
        $this->relationship_to_deceased = $this->finalRelationship;
    }

    public function getCurrentOptions(): array
    {
        if (empty($this->selectedPath)) {
            // 最初の選択肢
            return [
                'spouse' => '配偶者',
                'child' => '子',
                'parent' => '父母',
                'grandparent' => '祖父母',
                'sibling' => '兄弟姉妹',
                'other' => 'その他',
            ];
        }

        // 子が選択された場合は動的に選択肢を生成
        if ($this->selectedPath[0] === 'child') {
            return $this->generateDynamicChildOptions();
        }

        $current = $this->relationshipHierarchy;
        foreach ($this->selectedPath as $key) {
            if (isset($current[$key])) {
                $current = $current[$key];
            } else {
                return [];
            }
        }

        return $current['options'] ?? [];
    }

    public function canGoBack(): bool
    {
        return count($this->selectedPath) > 0;
    }

    public function isFinalSelection(): bool
    {
        if (empty($this->selectedPath)) {
            return false;
        }

        // 子が選択された場合の動的続柄処理
        if ($this->selectedPath[0] === 'child' && count($this->selectedPath) > 1) {
            return true; // 子の動的選択肢は最終選択
        }

        $current = $this->relationshipHierarchy;
        foreach ($this->selectedPath as $key) {
            if (isset($current[$key])) {
                $current = $current[$key];
            } else {
                return false;
            }
        }

        return empty($current['options']);
    }

    public function save(): void
    {
        $this->validate();

        // 続柄の値を決定
        $relationshipValue = $this->relationship_to_deceased;
        if ($this->finalRelationship === 'その他' && $this->custom_relationship) {
            $relationshipValue = $this->custom_relationship;
        }

        $this->person->update([
            'family_name' => $this->family_name,
            'given_name' => $this->given_name,
            'family_name_kana' => $this->family_name_kana,
            'given_name_kana' => $this->given_name_kana,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date,
            'death_date' => $this->death_date,
            'is_alive' => $this->is_alive,
            'legal_status' => $this->legal_status,
            'relationship_to_deceased' => $relationshipValue,
            'postal_code' => $this->postal_code,
            'current_address' => $this->current_address,
            'registered_domicile' => $this->registered_domicile,
            'registered_address' => $this->registered_address,
        ]);

        session()->flash('success', '家族構成員の情報を更新しました。');

        $this->redirect(route('family-trees.show', $this->familyTree), navigate: true);
    }
}; ?>

<div>
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- ヘッダー -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    家族構成員の編集
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $familyTree->title }} - {{ $person->full_name }}
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('family-trees.show', $familyTree) }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    キャンセル
                </a>
            </div>
        </div>

        <!-- フォーム -->
        <form wire:submit="save" class="space-y-8">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <!-- 基本情報 -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">基本情報</h3>
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
                    </div>

                    <!-- 生年月日 -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">生年月日</h3>
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
                    </div>

                    <!-- 住所情報 -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">住所情報</h3>
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
                                    <textarea wire:model="registered_domicile" id="registered_domicile" rows="2"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                </div>
                                @error('registered_domicile')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="registered_address"
                                    class="block text-sm font-medium text-gray-700">住民票住所</label>
                                <div class="mt-1">
                                    <textarea wire:model="registered_address" id="registered_address" rows="2"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                </div>
                                @error('registered_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 相続法上の地位 -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">相続法上の地位</h3>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="radio" wire:model="legal_status" id="legal_status_heir"
                                    value="heir" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                <label for="legal_status_heir" class="ml-3 block text-sm font-medium text-gray-700">
                                    相続人
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

                    <!-- 続柄 -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">被相続人との続柄</h3>

                        @if (!empty($selectedPath))
                            <div class="mt-2 mb-4">
                                <div class="flex items-center space-x-2 text-sm text-gray-600">
                                    <span>選択済み:</span>
                                    @php
                                        $displayPath = [];
                                        $current = $this->relationshipHierarchy;

                                        // 親カテゴリのラベルを追加
                                        if (count($selectedPath) > 0) {
                                            $parentKey = $selectedPath[0];
                                            if (isset($current[$parentKey])) {
                                                $displayPath[] = $current[$parentKey]['label'];
                                            }
                                        }

                                        // 子の選択肢のラベルを追加
                                        if (count($selectedPath) > 1) {
                                            $childKey = $selectedPath[1];

                                            // 子が選択された場合の動的続柄処理
                                            if ($selectedPath[0] === 'child') {
                                                $dynamicOptions = $this->generateDynamicChildOptions();
                                                if (isset($dynamicOptions[$childKey])) {
                                                    $displayPath[] = $dynamicOptions[$childKey];
                                                }
                                            } else {
                                                // 通常の階層構造から検索
                                                if (isset($current[$selectedPath[0]]['options'][$childKey])) {
                                                    $displayPath[] = $current[$selectedPath[0]]['options'][$childKey];
                                                }
                                            }
                                        }
                                    @endphp

                                    @foreach ($displayPath as $index => $label)
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                            {{ $label }}
                                        </span>
                                        @if ($index < count($displayPath) - 1)
                                            <span>→</span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-4">
                            @if (!$this->isFinalSelection())
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach ($this->getCurrentOptions() as $key => $label)
                                        <button type="button" wire:click="selectRelationship('{{ $key }}')"
                                            class="p-3 text-left border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <div class="text-sm font-medium text-gray-900">{{ $label }}
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="text-sm font-medium text-green-800">
                                        選択された続柄: {{ $finalRelationship }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if ($this->canGoBack())
                            <div class="mt-4">
                                <button type="button" wire:click="goBackRelationship"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                    戻る
                                </button>
                            </div>
                        @endif

                        @if ($finalRelationship === 'その他')
                            <div class="mt-4">
                                <label for="custom_relationship" class="block text-sm font-medium text-gray-700">
                                    その他の続柄
                                </label>
                                <div class="mt-1">
                                    <input type="text" wire:model="custom_relationship" id="custom_relationship"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        placeholder="続柄を入力してください">
                                </div>
                                @error('custom_relationship')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 保存ボタン -->
            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    更新
                </button>
            </div>
        </form>
    </div>
</div>
