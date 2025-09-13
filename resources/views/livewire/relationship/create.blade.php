<?php

use App\Models\FamilyTree;
use App\Models\Person;
use App\Models\Relationship;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public FamilyTree $familyTree;
    public Person $person1;
    public Person $person2;

    // タブ式UI用のプロパティ
    public array $selectedPath = [];
    public string $finalRelationship = '';

    // 関係性の階層構造
    public array $relationshipHierarchy = [
        'spouse' => [
            'label' => '配偶者',
            'options' => [
                'wife' => '妻',
                'husband' => '夫',
            ],
        ],
        'parent_child' => [
            'label' => '子',
            'options' => [
                'eldest_son' => '長男',
                'eldest_daughter' => '長女',
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
        'grandparent' => [
            'label' => '祖父母',
            'options' => [
                'grandfather' => '祖父',
                'grandmother' => '祖母',
            ],
        ],
        'grandchild' => [
            'label' => '孫',
            'options' => [
                'grandson' => '孫（息子）',
                'granddaughter' => '孫（娘）',
            ],
        ],
        'nephew_niece' => [
            'label' => '甥姪',
            'options' => [
                'nephew' => '甥',
                'niece' => '姪',
            ],
        ],
        'adopted' => [
            'label' => '養子関係',
            'options' => [
                'adopted_son' => '養子（息子）',
                'adopted_daughter' => '養子（娘）',
            ],
        ],
        'other' => [
            'label' => 'その他',
            'options' => [],
        ],
    ];

    // 従来のバリデーション用プロパティ
    #[Rule('required|in:parent_child,spouse,sibling,adopted_child,grandchild,nephew_niece,other')]
    public string $relationship_type = '';

    #[Rule('nullable|in:father,mother,husband,wife,eldest_son,eldest_daughter,second_son,second_daughter,third_son,third_daughter')]
    public ?string $parent_type = null;

    #[Rule('nullable|in:paternal,maternal')]
    public ?string $indirect_relationship = null;

    #[Rule('nullable|integer|min:1')]
    public ?int $relationship_order = null;

    #[Rule('nullable|date')]
    public ?string $relationship_date = null;

    #[Rule('nullable|string|max:1000')]
    public ?string $notes = null;

    #[Rule('nullable|string|max:255')]
    public ?string $custom_relationship = null;

    public function mount(FamilyTree $familyTree, Person $person1, Person $person2): void
    {
        $this->familyTree = $familyTree;
        $this->person1 = $person1;
        $this->person2 = $person2;
    }

    public function getExistingChildren(): array
    {
        // 既存の相続人の中で、親子関係の続柄を取得
        $existingChildren = [];

        // person1が被相続人の場合、person1の子の続柄をチェック
        if ($this->person1->is_deceased) {
            $children = Person::where('family_tree_id', $this->familyTree->id)->where('is_deceased', false)->where('legal_status', 'heir')->get();

            \Log::info('Found children:', $children->pluck('relationship_to_deceased')->toArray());

            foreach ($children as $child) {
                if ($child->relationship_to_deceased) {
                    $existingChildren[] = $child->relationship_to_deceased;
                }
            }
        }

        \Log::info('Existing children result:', $existingChildren);

        return $existingChildren;
    }

    public function getDynamicChildOptions(): array
    {
        // テスト用に固定の選択肢を返す
        $options = [
            'eldest_son' => '長男',
            'eldest_daughter' => '長女',
            'second_son' => '二男',
            'second_daughter' => '次女',
        ];

        \Log::info('Dynamic child options (test):', $options);

        return $options;
    }

    public function selectRelationship(string $key): void
    {
        \Log::info('selectRelationship called', ['key' => $key, 'currentPath' => $this->selectedPath]);

        $this->selectedPath[] = $key;

        \Log::info('selectedPath after adding', $this->selectedPath);

        // 最終選択かどうかをチェック
        if ($this->isFinalSelection()) {
            \Log::info('Final selection reached');
            $this->finalRelationship = $this->getFinalRelationshipLabel();
            $this->mapToLegacyFields();
        } else {
            \Log::info('Not final selection yet');
        }
    }

    public function goBackRelationship(): void
    {
        if (!empty($this->selectedPath)) {
            array_pop($this->selectedPath);
            $this->finalRelationship = '';
            $this->relationship_type = '';
            $this->parent_type = null;
            $this->indirect_relationship = null;
        }
    }

    public function isFinalSelection(): bool
    {
        if (empty($this->selectedPath)) {
            \Log::info('isFinalSelection: empty selectedPath');
            return false;
        }

        $current = $this->relationshipHierarchy;
        foreach ($this->selectedPath as $key) {
            if (!isset($current[$key])) {
                \Log::info('isFinalSelection: key not found', ['key' => $key]);
                return false;
            }
            $current = $current[$key];
        }

        // 現在のレベルにoptionsがある場合は、まだ選択できる
        if (isset($current['options']) && !empty($current['options'])) {
            \Log::info('isFinalSelection: has options, not final', ['options' => $current['options']]);
            return false;
        }

        // 親子関係の場合は、動的選択肢があるかチェック
        if ($this->selectedPath[0] === 'parent_child' && count($this->selectedPath) === 1) {
            $dynamicOptions = $this->getDynamicChildOptions();
            if (!empty($dynamicOptions)) {
                \Log::info('isFinalSelection: parent_child has dynamic options, not final', ['options' => $dynamicOptions]);
                return false;
            }
        }

        \Log::info('isFinalSelection: final selection', ['selectedPath' => $this->selectedPath]);
        return true;
    }

    public function getCurrentOptions(): array
    {
        if (empty($this->selectedPath)) {
            return array_map(fn($item) => $item['label'], $this->relationshipHierarchy);
        }

        $current = $this->relationshipHierarchy;
        foreach ($this->selectedPath as $key) {
            if (!isset($current[$key])) {
                return [];
            }
            $current = $current[$key];
        }

        // 現在のレベルにoptionsがある場合はそれを返す
        if (isset($current['options']) && !empty($current['options'])) {
            // 親子関係の場合は動的な選択肢を返す
            if ($this->selectedPath[0] === 'parent_child') {
                $dynamicOptions = $this->getDynamicChildOptions();
                \Log::info('Returning dynamic options for parent_child:', $dynamicOptions);
                return $dynamicOptions;
            }
            \Log::info('Returning static options:', $current['options']);
            return $current['options'];
        }

        \Log::info('No options found, returning empty array');
        return [];
    }

    public function canGoBack(): bool
    {
        return !empty($this->selectedPath);
    }

    public function getFinalRelationshipLabel(): string
    {
        if (empty($this->selectedPath)) {
            return '';
        }

        $current = $this->relationshipHierarchy;
        $labels = [];

        foreach ($this->selectedPath as $key) {
            if (isset($current[$key])) {
                $labels[] = $current[$key]['label'];
                $current = $current[$key]['options'] ?? [];
            }
        }

        return implode(' → ', $labels);
    }

    public function mapToLegacyFields(): void
    {
        if (empty($this->selectedPath)) {
            \Log::info('mapToLegacyFields: empty selectedPath');
            return;
        }

        $firstKey = $this->selectedPath[0];
        $secondKey = $this->selectedPath[1] ?? null;

        \Log::info('mapToLegacyFields called', [
            'selectedPath' => $this->selectedPath,
            'firstKey' => $firstKey,
            'secondKey' => $secondKey,
        ]);

        // 関係性タイプのマッピング
        switch ($firstKey) {
            case 'spouse':
                $this->relationship_type = 'spouse';
                // 配偶者の詳細選択（夫/妻）はparent_typeに格納
                if ($secondKey === 'husband' || $secondKey === 'wife') {
                    $this->parent_type = $secondKey;
                }
                break;
            case 'parent_child':
                $this->relationship_type = 'parent_child';
                if (in_array($secondKey, ['eldest_son', 'eldest_daughter', 'second_son', 'second_daughter', 'third_son', 'third_daughter'])) {
                    $this->parent_type = $secondKey;
                }
                break;
            case 'sibling':
                $this->relationship_type = 'sibling';
                break;
            case 'grandchild':
                $this->relationship_type = 'grandchild';
                break;
            case 'nephew_niece':
                $this->relationship_type = 'nephew_niece';
                break;
            case 'adopted':
                $this->relationship_type = 'adopted_child';
                break;
            case 'other':
                $this->relationship_type = 'other';
                break;
        }

        \Log::info('mapToLegacyFields result', [
            'relationship_type' => $this->relationship_type,
            'parent_type' => $this->parent_type,
        ]);
    }

    public function save(): void
    {
        \Log::info('Save method called', [
            'relationship_type' => $this->relationship_type,
            'parent_type' => $this->parent_type,
            'selectedPath' => $this->selectedPath,
            'finalRelationship' => $this->finalRelationship,
        ]);

        // カスタム関係性のバリデーション
        if ($this->relationship_type === 'other' && empty($this->custom_relationship)) {
            $this->addError('custom_relationship', 'その他の関係性を入力してください。');
            return;
        }

        $this->validate();

        // 既存の関係性をチェック
        $existingRelationship = Relationship::where('family_tree_id', $this->familyTree->id)
            ->where(function ($query) {
                $query
                    ->where(function ($q) {
                        $q->where('person1_id', $this->person1->id)->where('person2_id', $this->person2->id);
                    })
                    ->orWhere(function ($q) {
                        $q->where('person1_id', $this->person2->id)->where('person2_id', $this->person1->id);
                    });
            })
            ->where('relationship_type', $this->relationship_type)
            ->first();

        if ($existingRelationship) {
            $this->addError('relationship_type', 'この関係性は既に登録されています。');
            return;
        }

        Relationship::create([
            'family_tree_id' => $this->familyTree->id,
            'person1_id' => $this->person1->id,
            'person2_id' => $this->person2->id,
            'relationship_type' => $this->relationship_type,
            'parent_type' => $this->parent_type,
            'indirect_relationship' => $this->indirect_relationship,
            'relationship_order' => $this->relationship_order,
            'relationship_date' => $this->relationship_date,
            'notes' => $this->notes,
        ]);

        session()->flash('success', '関係性を登録しました。');
        $this->redirect(route('family-trees.show', $this->familyTree), navigate: true);
    }

    public function getRelationshipTypeOptions(): array
    {
        return [
            'parent_child' => '子',
            'spouse' => '配偶者関係',
            'sibling' => '兄弟姉妹関係',
            'adopted_child' => '養子関係',
            'grandchild' => '孫',
            'nephew_niece' => '甥姪',
        ];
    }
}; ?>

<div>
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">関係性の設定</h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $person1->full_name }} と {{ $person2->full_name }} の関係を設定します
                </p>
            </div>

            <form wire:submit="save" class="space-y-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="space-y-6">
                        <!-- 関係性の種類選択（プルタブ式） -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                関係性の種類
                            </label>

                            @if (!empty($selectedPath))
                                <div class="mt-2 mb-4">
                                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                                        <span>選択済み:</span>
                                        @foreach ($selectedPath as $index => $key)
                                            @php
                                                $current = $this->relationshipHierarchy;
                                                foreach (array_slice($selectedPath, 0, $index + 1) as $pathKey) {
                                                    if (isset($current[$pathKey])) {
                                                        $current = $current[$pathKey];
                                                    }
                                                }
                                                $label = $current['label'] ?? $key;
                                            @endphp
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                                {{ $label }}
                                            </span>
                                            @if ($index < count($selectedPath) - 1)
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
                                            <button type="button"
                                                wire:click="selectRelationship('{{ $key }}')"
                                                class="p-3 text-left border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <div class="text-sm font-medium text-gray-900">{{ $label }}
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                        <div class="text-sm font-medium text-green-800">
                                            選択された関係性: {{ $finalRelationship }}
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
                                        その他の関係性
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" wire:model="custom_relationship" id="custom_relationship"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                            placeholder="関係性を入力してください">
                                    </div>
                                    @error('custom_relationship')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            @error('relationship_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 配偶者の場合の詳細 -->
                        @if ($relationship_type === 'spouse')
                            <div>
                                <label class="block text-sm font-medium text-gray-700">配偶者の種別</label>
                                <div class="mt-2 space-y-2">
                                    <div class="flex items-center">
                                        <input type="radio" wire:model="parent_type" id="parent_type_husband"
                                            value="husband"
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="parent_type_husband"
                                            class="ml-3 block text-sm font-medium text-gray-700">
                                            夫
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" wire:model="parent_type" id="parent_type_wife"
                                            value="wife"
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="parent_type_wife"
                                            class="ml-3 block text-sm font-medium text-gray-700">
                                            妻
                                        </label>
                                    </div>
                                </div>
                                @error('parent_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <!-- 子の場合の詳細 -->
                        @if ($relationship_type === 'parent_child')
                            <div>
                                <label class="block text-sm font-medium text-gray-700">子の種別</label>

                                <!-- デバッグ情報（開発時のみ表示） -->
                                @if (config('app.debug'))
                                    <div class="mt-2 p-2 bg-gray-100 rounded text-xs">
                                        <strong>既存の子の続柄:</strong> {{ implode(', ', $this->getExistingChildren()) }}
                                    </div>
                                @endif

                                <div class="mt-2 space-y-2">
                                    @foreach ($this->getDynamicChildOptions() as $key => $label)
                                        <div class="flex items-center">
                                            <input type="radio" wire:model="parent_type"
                                                id="parent_type_{{ $key }}" value="{{ $key }}"
                                                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                            <label for="parent_type_{{ $key }}"
                                                class="ml-3 block text-sm font-medium text-gray-700">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('parent_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <!-- 孫・甥姪の場合の詳細 -->
                        @if (in_array($relationship_type, ['grandchild', 'nephew_niece']))
                            <div>
                                <label class="block text-sm font-medium text-gray-700">父方/母方の区別</label>
                                <div class="mt-2 space-y-2">
                                    <div class="flex items-center">
                                        <input type="radio" wire:model="indirect_relationship"
                                            id="indirect_relationship_paternal" value="paternal"
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="indirect_relationship_paternal"
                                            class="ml-3 block text-sm font-medium text-gray-700">
                                            父方
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" wire:model="indirect_relationship"
                                            id="indirect_relationship_maternal" value="maternal"
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="indirect_relationship_maternal"
                                            class="ml-3 block text-sm font-medium text-gray-700">
                                            母方
                                        </label>
                                    </div>
                                </div>
                                @error('indirect_relationship')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <!-- 関係の順序 -->
                        <div>
                            <label for="relationship_order" class="block text-sm font-medium text-gray-700">
                                関係の順序（第何子など）
                            </label>
                            <div class="mt-1">
                                <input type="number" wire:model="relationship_order" id="relationship_order"
                                    min="1"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('relationship_order')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 関係開始日 -->
                        <div>
                            <label for="relationship_date" class="block text-sm font-medium text-gray-700">
                                関係開始日（結婚日、養子縁組日など）
                            </label>
                            <div class="mt-1">
                                <input type="date" wire:model="relationship_date" id="relationship_date"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('relationship_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 備考 -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">備考</label>
                            <div class="mt-1">
                                <textarea wire:model="notes" id="notes" rows="3"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                            </div>
                            @error('notes')
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
                        登録
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
