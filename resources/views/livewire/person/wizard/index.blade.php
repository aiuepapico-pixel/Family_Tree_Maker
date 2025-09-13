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
        3 => ['title' => '住所情報', 'description' => '現住所を入力してください'],
        4 => ['title' => '続柄', 'description' => '被相続人との関係を入力してください'],
    ];

    // 続柄の階層構造（基本構造）
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
        'grandchild' => [
            'label' => '孫',
            'options' => [],
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

    // 既存の続柄データを取得
    public function getExistingRelationships(): array
    {
        return $this->familyTree->people()->whereNotNull('relationship_to_deceased')->pluck('relationship_to_deceased')->toArray();
    }

    // 死亡した相続人を取得
    public function getDeceasedHeirs(): array
    {
        $deceasedHeirs = $this->familyTree
            ->people()
            ->where('is_alive', false)
            ->whereNotNull('relationship_to_deceased')
            ->get()
            ->map(function ($person) {
                return [
                    'id' => $person->id,
                    'name' => $person->full_name,
                    'relationship' => $person->relationship_to_deceased,
                ];
            })
            ->toArray();

        return $deceasedHeirs;
    }

    // 動的に続柄選択肢を生成
    public function generateDynamicChildOptions(): array
    {
        $existingRelationships = $this->getExistingRelationships();
        $options = [];

        // 既存の続柄を分析（連続性を考慮）
        $existingSons = [];
        $existingDaughters = [];
        $hasAdopted = false;

        foreach ($existingRelationships as $relationship) {
            if ($relationship === '長男') {
                $existingSons[] = 1;
            } elseif ($relationship === '二男') {
                $existingSons[] = 2;
            } elseif ($relationship === '三男') {
                $existingSons[] = 3;
            } elseif ($relationship === '四男') {
                $existingSons[] = 4;
            } elseif ($relationship === '五男') {
                $existingSons[] = 5;
            } elseif ($relationship === '六男') {
                $existingSons[] = 6;
            } elseif ($relationship === '七男') {
                $existingSons[] = 7;
            } elseif ($relationship === '八男') {
                $existingSons[] = 8;
            } elseif ($relationship === '九男') {
                $existingSons[] = 9;
            } elseif ($relationship === '長女') {
                $existingDaughters[] = 1;
            } elseif ($relationship === '二女') {
                $existingDaughters[] = 2;
            } elseif ($relationship === '三女') {
                $existingDaughters[] = 3;
            } elseif ($relationship === '四女') {
                $existingDaughters[] = 4;
            } elseif ($relationship === '五女') {
                $existingDaughters[] = 5;
            } elseif ($relationship === '六女') {
                $existingDaughters[] = 6;
            } elseif ($relationship === '七女') {
                $existingDaughters[] = 7;
            } elseif ($relationship === '八女') {
                $existingDaughters[] = 8;
            } elseif ($relationship === '九女') {
                $existingDaughters[] = 9;
            } elseif ($relationship === '養子') {
                $hasAdopted = true;
            }
        }

        // 男の子の選択肢を生成（連続性を保つ）
        $maxSonNumber = empty($existingSons) ? 0 : max($existingSons);
        for ($i = 1; $i <= $maxSonNumber + 1 && $i <= 9; $i++) {
            if (!in_array($i, $existingSons)) {
                $key = $this->getSonKey($i);
                $label = $this->getSonLabel($i);
                $options[$key] = $label;
            }
        }

        // 女の子の選択肢を生成（連続性を保つ）
        $maxDaughterNumber = empty($existingDaughters) ? 0 : max($existingDaughters);
        for ($i = 1; $i <= $maxDaughterNumber + 1 && $i <= 9; $i++) {
            if (!in_array($i, $existingDaughters)) {
                $key = $this->getDaughterKey($i);
                $label = $this->getDaughterLabel($i);
                $options[$key] = $label;
            }
        }

        // 養子の選択肢（1人まで）
        if (!$hasAdopted) {
            $options['adopted_child'] = '養子';
        }

        return $options;
    }

    // 男の子のキーを取得
    private function getSonKey(int $number): string
    {
        return match ($number) {
            1 => 'eldest_son',
            2 => 'second_son',
            3 => 'third_son',
            4 => 'fourth_son',
            5 => 'fifth_son',
            6 => 'sixth_son',
            7 => 'seventh_son',
            8 => 'eighth_son',
            9 => 'ninth_son',
            default => 'son_' . $number,
        };
    }

    // 男の子のラベルを取得
    private function getSonLabel(int $number): string
    {
        return match ($number) {
            1 => '長男',
            2 => '二男',
            3 => '三男',
            4 => '四男',
            5 => '五男',
            6 => '六男',
            7 => '七男',
            8 => '八男',
            9 => '九男',
            default => $number . '男',
        };
    }

    // 女の子のキーを取得
    private function getDaughterKey(int $number): string
    {
        return match ($number) {
            1 => 'eldest_daughter',
            2 => 'second_daughter',
            3 => 'third_daughter',
            4 => 'fourth_daughter',
            5 => 'fifth_daughter',
            6 => 'sixth_daughter',
            7 => 'seventh_daughter',
            8 => 'eighth_daughter',
            9 => 'ninth_daughter',
            default => 'daughter_' . $number,
        };
    }

    // 女の子のラベルを取得
    private function getDaughterLabel(int $number): string
    {
        return match ($number) {
            1 => '長女',
            2 => '二女',
            3 => '三女',
            4 => '四女',
            5 => '五女',
            6 => '六女',
            7 => '七女',
            8 => '八女',
            9 => '九女',
            default => $number . '女',
        };
    }

    // 孫の選択肢を生成（死亡した相続人のリスト）
    public function generateGrandchildOptions(): array
    {
        $deceasedHeirs = $this->getDeceasedHeirs();
        $options = [];

        foreach ($deceasedHeirs as $heir) {
            $key = 'grandchild_of_' . $heir['id'];
            $label = $heir['name'] . '（' . $heir['relationship'] . '）の子';
            $options[$key] = $label;
        }

        return $options;
    }

    // 死亡した相続人の子の具体的な続柄選択肢を生成
    public function generateGrandchildRelationshipOptions($heirId): array
    {
        $options = [];

        // 基本的な子の続柄
        $basicRelationships = [
            'eldest_son' => '長男',
            'eldest_daughter' => '長女',
            'second_son' => '二男',
            'second_daughter' => '二女',
            'third_son' => '三男',
            'third_daughter' => '三女',
            'fourth_son' => '四男',
            'fourth_daughter' => '四女',
            'fifth_son' => '五男',
            'fifth_daughter' => '五女',
            'adopted_child' => '養子',
        ];

        // 既存の孫の続柄を取得（同じ死亡した相続人の子として既に登録されているもの）
        $existingGrandchildRelationships = $this->familyTree
            ->people()
            ->where('relationship_to_deceased', 'like', '%' . $this->getDeceasedHeirName($heirId) . '%')
            ->pluck('relationship_to_deceased')
            ->toArray();

        // 既存の続柄を分析
        $sonCount = 0;
        $daughterCount = 0;
        $adoptedCount = 0;

        foreach ($existingGrandchildRelationships as $relationship) {
            if (str_contains($relationship, '長男') || str_contains($relationship, '二男') || str_contains($relationship, '三男') || str_contains($relationship, '四男') || str_contains($relationship, '五男')) {
                $sonCount++;
            } elseif (str_contains($relationship, '長女') || str_contains($relationship, '二女') || str_contains($relationship, '三女') || str_contains($relationship, '四女') || str_contains($relationship, '五女')) {
                $daughterCount++;
            } elseif (str_contains($relationship, '養子')) {
                $adoptedCount++;
            }
        }

        // 長男・長女が選択されていない場合は表示
        if (!in_array($this->getDeceasedHeirName($heirId) . '（' . $this->getDeceasedHeirRelationship($heirId) . '）の長男', $existingGrandchildRelationships)) {
            $options['eldest_son'] = '長男';
        }
        if (!in_array($this->getDeceasedHeirName($heirId) . '（' . $this->getDeceasedHeirRelationship($heirId) . '）の長女', $existingGrandchildRelationships)) {
            $options['eldest_daughter'] = '長女';
        }

        // 二男・二女の選択肢を生成
        if ($sonCount >= 1 && !in_array($this->getDeceasedHeirName($heirId) . '（' . $this->getDeceasedHeirRelationship($heirId) . '）の二男', $existingGrandchildRelationships)) {
            $options['second_son'] = '二男';
        }
        if ($daughterCount >= 1 && !in_array($this->getDeceasedHeirName($heirId) . '（' . $this->getDeceasedHeirRelationship($heirId) . '）の二女', $existingGrandchildRelationships)) {
            $options['second_daughter'] = '二女';
        }

        // 三男・三女の選択肢を生成
        if ($sonCount >= 2 && !in_array($this->getDeceasedHeirName($heirId) . '（' . $this->getDeceasedHeirRelationship($heirId) . '）の三男', $existingGrandchildRelationships)) {
            $options['third_son'] = '三男';
        }
        if ($daughterCount >= 2 && !in_array($this->getDeceasedHeirName($heirId) . '（' . $this->getDeceasedHeirRelationship($heirId) . '）の三女', $existingGrandchildRelationships)) {
            $options['third_daughter'] = '三女';
        }

        // 四男・四女の選択肢を生成
        if ($sonCount >= 3 && !in_array($this->getDeceasedHeirName($heirId) . '（' . $this->getDeceasedHeirRelationship($heirId) . '）の四男', $existingGrandchildRelationships)) {
            $options['fourth_son'] = '四男';
        }
        if ($daughterCount >= 3 && !in_array($this->getDeceasedHeirName($heirId) . '（' . $this->getDeceasedHeirRelationship($heirId) . '）の四女', $existingGrandchildRelationships)) {
            $options['fourth_daughter'] = '四女';
        }

        // 五男・五女の選択肢を生成
        if ($sonCount >= 4 && !in_array($this->getDeceasedHeirName($heirId) . '（' . $this->getDeceasedHeirRelationship($heirId) . '）の五男', $existingGrandchildRelationships)) {
            $options['fifth_son'] = '五男';
        }
        if ($daughterCount >= 4 && !in_array($this->getDeceasedHeirName($heirId) . '（' . $this->getDeceasedHeirRelationship($heirId) . '）の五女', $existingGrandchildRelationships)) {
            $options['fifth_daughter'] = '五女';
        }

        // 養子の選択肢
        if ($adoptedCount === 0) {
            $options['adopted_child'] = '養子';
        }

        return $options;
    }

    // 死亡した相続人の名前を取得
    private function getDeceasedHeirName($heirId): string
    {
        $deceasedHeirs = $this->getDeceasedHeirs();
        foreach ($deceasedHeirs as $heir) {
            if ($heir['id'] === $heirId) {
                return $heir['name'];
            }
        }
        return '';
    }

    // 死亡した相続人の続柄を取得
    private function getDeceasedHeirRelationship($heirId): string
    {
        $deceasedHeirs = $this->getDeceasedHeirs();
        foreach ($deceasedHeirs as $heir) {
            if ($heir['id'] === $heirId) {
                return $heir['relationship'];
            }
        }
        return '';
    }

    // 現在の選択パス
    public array $selectedPath = [];
    public string $finalRelationship = '';

    // 孫選択時の死亡相続人選択
    public ?int $selectedDeceasedHeirId = null;
    public ?string $selectedDeceasedHeirRelationship = null;

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

    // ステップ4: 続柄
    #[Rule('required|in:heir,renounced', message: '相続法上の地位を選択してください')]
    public string $legal_status = 'heir';

    #[Rule('nullable|string|max:100', message: 'その他の続柄は100文字以内で入力してください')]
    public ?string $custom_relationship = null;

    // 最終的な続柄（データベース保存用）
    public ?string $relationship_to_deceased = null;

    public function mount(FamilyTree $familyTree): void
    {
        $this->authorize('update', $familyTree);
        $this->familyTree = $familyTree;

        // URLパラメータからステップを設定
        if (request()->has('step')) {
            $step = (int) request()->get('step');
            if ($step >= 1 && $step <= count($this->steps)) {
                $this->currentStep = $step;
            }
        }
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

    public function selectRelationship($key): void
    {
        $this->selectedPath[] = $key;

        // 最終的な続柄を更新
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
            $this->selectedDeceasedHeirId = null;
            $this->selectedDeceasedHeirRelationship = null;
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

        // 孫が選択された場合の動的続柄処理
        if ($this->selectedPath[0] === 'grandchild' && count($this->selectedPath) > 1) {
            $grandchildKey = $this->selectedPath[1];

            // 死亡した相続人が選択された場合（まだ具体的な続柄を選択していない）
            if (str_starts_with($grandchildKey, 'grandchild_of_') && count($this->selectedPath) === 2) {
                $heirId = (int) str_replace('grandchild_of_', '', $grandchildKey);

                // 死亡した相続人の情報を設定（続柄はまだ設定しない）
                $deceasedHeirs = $this->getDeceasedHeirs();
                foreach ($deceasedHeirs as $heir) {
                    if ($heir['id'] === $heirId) {
                        $this->selectedDeceasedHeirId = $heir['id'];
                        $this->selectedDeceasedHeirRelationship = $heir['relationship'];
                        $this->finalRelationship = $heir['name'] . '（' . $heir['relationship'] . '）の子';
                        $this->relationship_to_deceased = null; // まだ最終的な続柄ではない
                        break;
                    }
                }
                return;
            } elseif (count($this->selectedPath) > 2) {
                // 具体的な続柄が選択された場合（長男、長女など）
                $heirId = (int) str_replace('grandchild_of_', '', $this->selectedPath[1]);
                $relationshipKey = $this->selectedPath[2];
                $relationshipOptions = $this->generateGrandchildRelationshipOptions($heirId);

                if (isset($relationshipOptions[$relationshipKey])) {
                    $heirName = $this->getDeceasedHeirName($heirId);
                    $heirRelationship = $this->getDeceasedHeirRelationship($heirId);
                    $this->finalRelationship = $heirName . '（' . $heirRelationship . '）の' . $relationshipOptions[$relationshipKey];
                    $this->relationship_to_deceased = $this->finalRelationship;

                    // 死亡した相続人の情報を設定
                    $this->selectedDeceasedHeirId = $heirId;
                    $this->selectedDeceasedHeirRelationship = $heirRelationship;
                    return;
                }
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
            $options = [
                'spouse' => '配偶者',
                'child' => '子',
                'parent' => '父母',
                'grandparent' => '祖父母',
                'sibling' => '兄弟姉妹',
                'other' => 'その他',
            ];

            // 死亡した相続人がいる場合は孫を追加
            if (!empty($this->getDeceasedHeirs())) {
                $options['grandchild'] = '孫';
            }

            return $options;
        }

        // 子が選択された場合は動的に選択肢を生成
        if ($this->selectedPath[0] === 'child') {
            return $this->generateDynamicChildOptions();
        }

        // 孫が選択された場合は動的に選択肢を生成
        if ($this->selectedPath[0] === 'grandchild') {
            // 孫の死亡した相続人が選択されている場合は、具体的な続柄を表示
            if (count($this->selectedPath) > 1) {
                $heirId = (int) str_replace('grandchild_of_', '', $this->selectedPath[1]);
                return $this->generateGrandchildRelationshipOptions($heirId);
            }
            // 死亡した相続人のリストを表示
            return $this->generateGrandchildOptions();
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

        // 孫が選択された場合の動的続柄処理
        if ($this->selectedPath[0] === 'grandchild' && count($this->selectedPath) > 2) {
            return true; // 孫の具体的な続柄選択は最終選択
        }

        // 孫の死亡した相続人が選択されたが、まだ具体的な続柄を選択していない場合は最終選択ではない
        if ($this->selectedPath[0] === 'grandchild' && count($this->selectedPath) === 2) {
            return false;
        }

        // 孫が選択されたが、まだ具体的な死亡相続人を選択していない場合は最終選択ではない
        if ($this->selectedPath[0] === 'grandchild' && count($this->selectedPath) === 1) {
            return false;
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
            ],
            4 => [
                'legal_status' => 'required|in:heir,renounced',
                'finalRelationship' => 'required|string',
                'custom_relationship' => 'nullable|string|max:100',
            ],
            default => [],
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

        // 続柄の値を決定
        $relationshipValue = $this->relationship_to_deceased;
        if ($this->finalRelationship === 'その他' && $this->custom_relationship) {
            $relationshipValue = $this->custom_relationship;
        }

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
            'relationship_to_deceased' => $relationshipValue,
            'postal_code' => $this->postal_code,
            'current_address' => $this->current_address,
        ]);

        session()->flash('success', '相続人情報を登録しました。');

        $this->redirect(route('family-trees.show', $this->familyTree), navigate: true);
    }
}; ?>

<div>
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- ヘッダー -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    相続人情報の登録
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $familyTree->title }}
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('family-trees.show', $familyTree) }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    家系図を表示
                </a>
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
                                    <input type="checkbox" wire:model.live="is_alive" id="is_alive"
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
                                <label class="block text-sm font-medium text-gray-700">
                                    被相続人との続柄
                                </label>

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
                                                    } elseif ($selectedPath[0] === 'grandchild') {
                                                        // 孫が選択された場合の動的続柄処理
                                                        if (str_starts_with($childKey, 'grandchild_of_')) {
                                                            // 死亡した相続人が選択された場合
                                                            $dynamicOptions = $this->generateGrandchildOptions();
                                                            if (isset($dynamicOptions[$childKey])) {
                                                                $displayPath[] = $dynamicOptions[$childKey];
                                                            }
                                                        } else {
                                                            // 具体的な続柄が選択された場合
                                                            $heirId = (int) str_replace(
                                                                'grandchild_of_',
                                                                '',
                                                                $selectedPath[1],
                                                            );
                                                            $relationshipOptions = $this->generateGrandchildRelationshipOptions(
                                                                $heirId,
                                                            );
                                                            if (isset($relationshipOptions[$childKey])) {
                                                                $heirName = $this->getDeceasedHeirName($heirId);
                                                                $heirRelationship = $this->getDeceasedHeirRelationship(
                                                                    $heirId,
                                                                );
                                                                $displayPath[] =
                                                                    $heirName .
                                                                    '（' .
                                                                    $heirRelationship .
                                                                    '）の' .
                                                                    $relationshipOptions[$childKey];
                                                            }
                                                        }
                                                    } else {
                                                        // 通常の階層構造から検索
                                                        if (isset($current[$selectedPath[0]]['options'][$childKey])) {
                                                            $displayPath[] =
                                                                $current[$selectedPath[0]]['options'][$childKey];
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
                                        @php
                                            $currentOptions = $this->getCurrentOptions();
                                        @endphp


                                        <div class="grid grid-cols-2 gap-3">
                                            @foreach ($currentOptions as $key => $label)
                                                <button type="button"
                                                    wire:click="selectRelationship('{{ $key }}')"
                                                    class="p-3 text-left border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $label }}
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
                                        <label for="custom_relationship"
                                            class="block text-sm font-medium text-gray-700">
                                            その他の続柄
                                        </label>
                                        <div class="mt-1">
                                            <input type="text" wire:model="custom_relationship"
                                                id="custom_relationship"
                                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                                placeholder="続柄を入力してください">
                                        </div>
                                        @error('custom_relationship')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endif

                                @error('finalRelationship')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ナビゲーションボタン -->
            <div class="flex justify-between">
                <div class="flex space-x-3">
                    <a href="{{ route('family-trees.show', $familyTree) }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        家系図に戻る
                    </a>
                    <button type="button" wire:click="previousStep"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        @if ($currentStep === 1) disabled @endif>
                        前へ
                    </button>
                </div>

                @if ($currentStep < count($steps))
                    <button type="button" wire:click="nextStep"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        次へ
                    </button>
                @else
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        相続人を登録
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
