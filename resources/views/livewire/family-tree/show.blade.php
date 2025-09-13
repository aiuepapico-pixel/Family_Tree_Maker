<?php

use App\Models\FamilyTree;
use App\Models\Person;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public FamilyTree $familyTree;
    public ?int $personToDelete = null;

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

    public function mount(FamilyTree $familyTree): void
    {
        \Log::info('FamilyTreeShow component mounted', ['family_tree_id' => $familyTree->id]);
        $this->authorize('view', $familyTree);
        $this->familyTree = $familyTree;
    }

    // 続柄を階層表示に変換するメソッド
    public function getRelationshipDisplay(string $relationship): string
    {
        // 子の続柄パターンをチェック（長男、二女、養子など）
        $childPatterns = ['長男', '二男', '三男', '四男', '五男', '六男', '七男', '八男', '九男', '長女', '二女', '三女', '四女', '五女', '六女', '七女', '八女', '九女', '養子'];

        foreach ($childPatterns as $pattern) {
            if ($relationship === $pattern) {
                return '子(' . $relationship . ')';
            }
        }

        // 既存の続柄データを取得
        $existingRelationships = $this->familyTree->people()->whereNotNull('relationship_to_deceased')->pluck('relationship_to_deceased')->toArray();

        // 動的に生成された子の続柄をチェック
        $dynamicChildOptions = $this->generateDynamicChildOptions();

        // 動的に生成された子の続柄の場合
        if (in_array($relationship, $dynamicChildOptions)) {
            return '子(' . $relationship . ')'; // 子(長男)、子(二女)など
        }

        // 階層構造から検索
        foreach ($this->relationshipHierarchy as $category => $data) {
            if (isset($data['options'])) {
                foreach ($data['options'] as $key => $label) {
                    if ($label === $relationship) {
                        return $data['label'] . '(' . $label . ')';
                    }
                }
            }
        }

        // 見つからない場合はそのまま返す
        return $relationship;
    }

    // 動的に子の続柄選択肢を生成（wizard/index.blade.phpと同じロジック）
    public function generateDynamicChildOptions(): array
    {
        $existingRelationships = $this->familyTree->people()->whereNotNull('relationship_to_deceased')->pluck('relationship_to_deceased')->toArray();

        $options = [];

        // 既存の続柄を分析
        $sonCount = 0;
        $daughterCount = 0;
        $adoptedCount = 0;

        foreach ($existingRelationships as $relationship) {
            if (str_contains($relationship, '長男') || str_contains($relationship, '二男') || str_contains($relationship, '三男') || str_contains($relationship, '四男') || str_contains($relationship, '五男') || str_contains($relationship, '六男') || str_contains($relationship, '七男') || str_contains($relationship, '八男') || str_contains($relationship, '九男')) {
                $sonCount++;
            } elseif (str_contains($relationship, '長女') || str_contains($relationship, '二女') || str_contains($relationship, '三女') || str_contains($relationship, '四女') || str_contains($relationship, '五女') || str_contains($relationship, '六女') || str_contains($relationship, '七女') || str_contains($relationship, '八女') || str_contains($relationship, '九女')) {
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

        // 六男・六女の選択肢を生成
        if ($sonCount >= 5 && !in_array('六男', $existingRelationships)) {
            $options['sixth_son'] = '六男';
        }
        if ($daughterCount >= 5 && !in_array('六女', $existingRelationships)) {
            $options['sixth_daughter'] = '六女';
        }

        // 七男・七女の選択肢を生成
        if ($sonCount >= 6 && !in_array('七男', $existingRelationships)) {
            $options['seventh_son'] = '七男';
        }
        if ($daughterCount >= 6 && !in_array('七女', $existingRelationships)) {
            $options['seventh_daughter'] = '七女';
        }

        // 八男・八女の選択肢を生成
        if ($sonCount >= 7 && !in_array('八男', $existingRelationships)) {
            $options['eighth_son'] = '八男';
        }
        if ($daughterCount >= 7 && !in_array('八女', $existingRelationships)) {
            $options['eighth_daughter'] = '八女';
        }

        // 九男・九女の選択肢を生成
        if ($sonCount >= 8 && !in_array('九男', $existingRelationships)) {
            $options['ninth_son'] = '九男';
        }
        if ($daughterCount >= 8 && !in_array('九女', $existingRelationships)) {
            $options['ninth_daughter'] = '九女';
        }

        // 養子の選択肢
        if ($adoptedCount === 0) {
            $options['adopted_child'] = '養子';
        }

        return $options;
    }

    // 削除確認ダイアログを表示
    public function confirmDelete(int $personId): void
    {
        $this->personToDelete = $personId;
    }

    // 削除をキャンセル
    public function cancelDelete(): void
    {
        $this->personToDelete = null;
    }

    // 人物を削除
    public function deletePerson(): void
    {
        if (!$this->personToDelete) {
            return;
        }

        $person = Person::find($this->personToDelete);

        if (!$person) {
            $this->addError('delete', '人物が見つかりません。');
            return;
        }

        // 権限チェック
        $this->authorize('delete', $person);

        try {
            $person->delete();
            $this->personToDelete = null;
            session()->flash('success', '人物を削除しました。');
        } catch (\Exception $e) {
            $this->addError('delete', '削除中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    public function with(): array
    {
        return [
            'deceasedPerson' => $this->familyTree->people()->deceasedPerson()->first(),
            'familyMembers' => $this->familyTree->people()->familyMembers()->orderBy('generation_level')->orderBy('display_order')->get(),
            'relationships' => $this->familyTree
                ->relationships()
                ->with(['person1', 'person2'])
                ->get(),
        ];
    }
}; ?>

<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <!-- ヘッダー部分 -->
            <div class="mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">{{ $familyTree->title }}</h1>
                        <p class="mt-2 text-sm text-gray-500">{{ $familyTree->description }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('family-trees.visual', $familyTree) }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            視覚的表示
                        </a>
                        <a href="{{ route('family-trees.edit', $familyTree) }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            編集
                        </a>
                        <a href="{{ route('family-trees.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            一覧に戻る
                        </a>
                    </div>
                </div>

                <div class="mt-4 flex items-center space-x-4">
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
                    @if ($familyTree->deceased_person_id)
                        <span class="text-sm text-gray-500">
                            被相続人: {{ $familyTree->deceasedPerson->full_name }}
                        </span>
                    @endif
                    @if ($familyTree->inheritance_date)
                        <span class="text-sm text-gray-500">
                            相続開始日: {{ $familyTree->inheritance_date->format('Y年n月j日') }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- 被相続人表示エリア -->
            @if ($deceasedPerson)
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-medium text-red-900">被相続人</h2>
                            <p class="text-sm text-red-600">この家系図の被相続人（故人）</p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('deceased-person.edit', $familyTree) }}"
                                class="inline-flex items-center px-3 py-1 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                                編集
                            </a>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="bg-white border border-red-200 rounded-lg p-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 font-medium text-lg">
                                            {{ mb_substr($deceasedPerson->family_name, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900">{{ $deceasedPerson->full_name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $deceasedPerson->full_name_kana }}</p>
                                    <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500">
                                        <span>{{ $deceasedPerson->gender === 'male' ? '男性' : '女性' }}</span>
                                        @if ($deceasedPerson->birth_date)
                                            <span>生年月日: {{ $deceasedPerson->birth_date->format('Y年n月j日') }}</span>
                                        @endif
                                        @if ($deceasedPerson->death_date)
                                            <span>死亡日: {{ $deceasedPerson->death_date->format('Y年n月j日') }}</span>
                                        @endif
                                    </div>
                                    @if ($deceasedPerson->current_address || $deceasedPerson->registered_domicile || $deceasedPerson->registered_address)
                                        <div class="mt-2 text-sm text-gray-500">
                                            @if ($deceasedPerson->current_address)
                                                <p>現住所: {{ $deceasedPerson->current_address }}</p>
                                            @endif
                                            @if ($deceasedPerson->registered_domicile)
                                                <p>本籍地: {{ $deceasedPerson->registered_domicile }}</p>
                                            @endif
                                            @if ($deceasedPerson->registered_address)
                                                <p>住民票住所: {{ $deceasedPerson->registered_address }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-medium text-yellow-900">被相続人が未設定</h2>
                            <p class="text-sm text-yellow-600">まず被相続人を登録してください</p>
                        </div>
                        <a href="{{ route('deceased-person.wizard', $familyTree) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                            被相続人を登録
                        </a>
                    </div>
                </div>
            @endif

            <!-- 家族構成員表示エリア -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="mb-4 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">家族構成員</h2>
                    @if ($deceasedPerson)
                        <a href="{{ route('persons.wizard', $familyTree) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            人物を追加
                        </a>
                    @endif
                </div>

                @if ($familyMembers->count() > 0)
                    <!-- 世代ごとの人物リスト -->
                    @foreach ($familyMembers->groupBy('generation_level') as $level => $generationPeople)
                        <div class="mb-8">
                            <h3 class="text-sm font-medium text-gray-500 mb-4">
                                {{ $level === 0 ? '基準世代' : ($level > 0 ? $level . '世代下' : abs($level) . '世代上') }}
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($generationPeople as $person)
                                    <div
                                        class="border rounded-lg p-4 @if (!$person->is_alive) bg-gray-50 @endif">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="text-lg font-medium text-gray-900">
                                                    {{ $person->full_name }}
                                                </h4>
                                                <p class="text-sm text-gray-500">{{ $person->full_name_kana }}</p>
                                            </div>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if ($person->is_alive) bg-green-100 text-green-800 @else bg-gray-100 text-gray-800 @endif">
                                                {{ $person->is_alive ? '生存' : '死亡' }}
                                            </span>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-500">
                                            <p>生年月日: {{ $person->birth_date?->format('Y年n月j日') }}</p>
                                            @if (!$person->is_alive && $person->death_date)
                                                <p>死亡年月日: {{ $person->death_date->format('Y年n月j日') }}</p>
                                            @endif
                                            @if ($person->relationship_to_deceased)
                                                <p>続柄:
                                                    {{ $this->getRelationshipDisplay($person->relationship_to_deceased) }}
                                                </p>
                                            @endif
                                            @if ($person->current_address)
                                                <p>現住所: {{ $person->current_address }}</p>
                                            @endif
                                            @if ($person->registered_domicile)
                                                <p>本籍地: {{ $person->registered_domicile }}</p>
                                            @endif
                                            @if ($person->registered_address)
                                                <p>住民票住所: {{ $person->registered_address }}</p>
                                            @endif
                                        </div>
                                        <div class="mt-2 flex space-x-2">
                                            <a href="{{ route('persons.edit', ['familyTree' => $familyTree, 'person' => $person]) }}"
                                                class="text-xs text-blue-600 hover:text-blue-900">
                                                編集
                                            </a>
                                            <button wire:click="confirmDelete({{ $person->id }})"
                                                class="text-xs text-red-600 hover:text-red-900">
                                                削除
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-12">
                        <div class="mx-auto h-12 w-12 text-gray-400">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">家族構成員がいません</h3>
                        <p class="mt-1 text-sm text-gray-500">被相続人を登録した後、家族構成員を追加してください。</p>
                    </div>
                @endif

                <!-- 関係性の表示 -->
                @if ($relationships->isNotEmpty())
                    <div class="mt-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">関係性一覧</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            関係者1
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            関係
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            関係者2
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            詳細
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($relationships as $relationship)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $relationship->person1->full_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $relationship->relationship_description }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $relationship->person2->full_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if ($relationship->relationship_date)
                                                    {{ $relationship->relationship_date->format('Y年n月j日') }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 削除確認ダイアログ -->
    @if ($personToDelete)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
            wire:click="cancelDelete">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mt-4">人物を削除しますか？</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            この操作は取り消せません。この人物と関連するすべての関係性も削除されます。
                        </p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button wire:click="deletePerson"
                            class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                            削除
                        </button>
                        <button wire:click="cancelDelete"
                            class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            キャンセル
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- 成功メッセージ -->
    @if (session('success'))
        <div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50"
            x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            {{ session('success') }}
        </div>
    @endif

    <!-- エラーメッセージ -->
    @error('delete')
        <div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50"
            x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            {{ $message }}
        </div>
    @enderror

</div>
