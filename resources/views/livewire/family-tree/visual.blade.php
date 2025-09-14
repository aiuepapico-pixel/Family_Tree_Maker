<?php

use App\Models\FamilyTree;
use App\Models\Person;
use App\Models\Relationship;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public FamilyTree $familyTree;
    public $selectedPerson = null;
    public $showRelationshipForm = false;

    public function mount(FamilyTree $familyTree): void
    {
        $this->familyTree = $familyTree;
    }

    public function with(): array
    {
        return [
            'people' => $this->familyTree->people()->orderBy('generation_level')->orderBy('display_order')->get(),
            'relationships' => $this->familyTree
                ->relationships()
                ->with(['person1', 'person2'])
                ->get(),
            'deceasedPerson' => $this->getDeceasedPerson(),
            'heirs' => $this->getHeirs(),
        ];
    }

    /**
     * 被相続人を取得
     */
    public function getDeceasedPerson(): ?Person
    {
        return $this->familyTree->people()->where('id', $this->familyTree->deceased_person_id)->first();
    }

    /**
     * 相続人を取得
     */
    public function getHeirs(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->familyTree->people()->where('id', '!=', $this->familyTree->deceased_person_id)->orderBy('birth_date')->get();
    }

    public function selectPerson($personId): void
    {
        $this->selectedPerson = $personId;
    }

    public function createRelationship($person1Id, $person2Id): void
    {
        $this->redirect(
            route('relationships.create', [
                'familyTree' => $this->familyTree,
                'person1' => $person1Id,
                'person2' => $person2Id,
            ]),
            navigate: true,
        );
    }

    public function getPersonPosition($person): array
    {
        // 被相続人の場合は左側やや上に配置
        if ($person->isDeceasedPerson()) {
            return ['x' => 100, 'y' => 300];
        }

        // 相続人を分類
        $heirs = $this->getHeirs();
        $spouses = $heirs->filter(function ($heir) {
            return $heir->relationship_to_deceased && (str_contains($heir->relationship_to_deceased, '配偶者') || str_contains($heir->relationship_to_deceased, '妻') || str_contains($heir->relationship_to_deceased, '夫'));
        });

        $otherHeirs = $heirs->filter(function ($heir) {
            return !$heir->relationship_to_deceased || (!str_contains($heir->relationship_to_deceased, '配偶者') && !str_contains($heir->relationship_to_deceased, '妻') && !str_contains($heir->relationship_to_deceased, '夫'));
        });

        // 配偶者の場合は被相続人の下に配置
        if ($spouses->contains('id', $person->id)) {
            $spouseIndex = $spouses->search(function ($item) use ($person) {
                return $item->id === $person->id;
            });
            return ['x' => 100, 'y' => 500 + $spouseIndex * 120];
        }

        // その他の相続人は右側に生年月日順に配置
        $sortedOtherHeirs = $otherHeirs->sortBy('birth_date');
        $index = $sortedOtherHeirs->search(function ($item) use ($person) {
            return $item->id === $person->id;
        });

        // 相続人を右側に縦に並べる
        $x = 600; // 相続人エリアのX座標
        $y = 100 + $index * 150; // 各相続人の間隔を150px

        return ['x' => $x, 'y' => $y];
    }

    /**
     * 相続関係説明図用の人物配置を取得
     */
    public function getInheritanceDiagramPositions(): array
    {
        $positions = [];

        // 被相続人を左側やや上に配置
        if ($this->deceasedPerson) {
            $positions[$this->deceasedPerson->id] = [
                'x' => 100,
                'y' => 300,
                'type' => 'deceased',
            ];
        }

        // 相続人を世代別に左から配置
        $generationGroups = $this->heirs->groupBy('generation_level');
        $currentX = 100; // 左端のX座標

        foreach ($generationGroups as $generation => $people) {
            $y = 100 + ($generation - 1) * 150; // 被相続人を基準に上下配置

            foreach ($people as $index => $person) {
                $positions[$person->id] = [
                    'x' => $currentX + $index * 150,
                    'y' => $y,
                    'type' => 'heir',
                ];
            }
        }

        return $positions;
    }

    public function getRelationshipPath($relationship): string
    {
        $person1Pos = $this->getPersonPosition($relationship->person1);
        $person2Pos = $this->getPersonPosition($relationship->person2);

        $x1 = $person1Pos['x'];
        $y1 = $person1Pos['y'];
        $x2 = $person2Pos['x'];
        $y2 = $person2Pos['y'];

        // 関係性に応じた線の描画
        switch ($relationship->relationship_type) {
            case 'parent_child':
                // 親子関係：縦線
                return "M {$x1} {$y1} L {$x2} {$y2}";
            case 'spouse':
                // 配偶者関係：横線
                return "M {$x1} {$y1} L {$x2} {$y2}";
            case 'sibling':
                // 兄弟関係：水平線
                return "M {$x1} {$y1} L {$x2} {$y2}";
            default:
                return "M {$x1} {$y1} L {$x2} {$y2}";
        }
    }

    /**
     * 相続関係説明図用の関係線のスタイルを取得
     */
    public function getRelationshipStyle($relationship): array
    {
        switch ($relationship->relationship_type) {
            case 'spouse':
                // 配偶者関係：二重線
                return [
                    'stroke' => '#374151',
                    'strokeWidth' => 3,
                    'strokeDasharray' => null,
                    'isDouble' => true,
                ];
            case 'parent_child':
                // 親子関係：実線
                return [
                    'stroke' => '#374151',
                    'strokeWidth' => 2,
                    'strokeDasharray' => null,
                    'isDouble' => false,
                ];
            case 'adopted_child':
                // 養子関係：点線
                return [
                    'stroke' => '#6B7280',
                    'strokeWidth' => 2,
                    'strokeDasharray' => '5,5',
                    'isDouble' => false,
                ];
            default:
                return [
                    'stroke' => '#6B7280',
                    'strokeWidth' => 1,
                    'strokeDasharray' => null,
                    'isDouble' => false,
                ];
        }
    }
}; ?>

<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <!-- ヘッダー -->
            <div class="mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">{{ $familyTree->title }}</h1>
                        <p class="mt-2 text-sm text-gray-500">{{ $familyTree->description }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('family-trees.show', $familyTree) }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            詳細表示
                        </a>
                        <a href="{{ route('family-trees.edit', $familyTree) }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            編集
                        </a>
                    </div>
                </div>
            </div>

            <!-- 家系図表示エリア -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="mb-4 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">相続関係説明図</h2>
                        <p class="text-sm text-gray-500">
                            総人数: {{ $people->count() }}人
                            @if ($deceasedPerson)
                                | 被相続人: {{ $deceasedPerson->full_name }}
                            @endif
                            | 相続人: {{ $heirs->count() }}人
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <button type="button" onclick="zoomIn()"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            拡大
                        </button>
                        <button type="button" onclick="zoomOut()"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            縮小
                        </button>
                        <button type="button" onclick="resetZoom()"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            リセット
                        </button>
                    </div>
                </div>

                <!-- 相続関係説明図 -->
                <div class="overflow-auto border rounded-lg" style="height: 800px;">
                    <svg id="inheritance-diagram-svg" width="100%" height="100%" viewBox="0 0 1400 1000"
                        style="min-width: 1400px; min-height: 1000px;">
                        <!-- 表題 -->
                        <text x="700" y="50" text-anchor="middle" class="text-lg font-bold fill-gray-900">
                            被相続人 {{ $deceasedPerson ? $deceasedPerson->full_name : '未設定' }} 相続関係説明図
                        </text>

                        <!-- 関係線 -->
                        @foreach ($relationships as $relationship)
                            @php
                                $style = $this->getRelationshipStyle($relationship);
                            @endphp
                            @if ($style['isDouble'])
                                <!-- 二重線（配偶者関係） -->
                                <path d="{{ $this->getRelationshipPath($relationship) }}"
                                    stroke="{{ $style['stroke'] }}" stroke-width="{{ $style['strokeWidth'] }}"
                                    stroke-dasharray="{{ $style['strokeDasharray'] }}" fill="none"
                                    class="relationship-line" />
                                <path d="{{ $this->getRelationshipPath($relationship) }}"
                                    stroke="{{ $style['stroke'] }}" stroke-width="1"
                                    stroke-dasharray="{{ $style['strokeDasharray'] }}" fill="none"
                                    class="relationship-line" style="transform: translateY(2px);" />
                            @else
                                <!-- 通常の線 -->
                                <path d="{{ $this->getRelationshipPath($relationship) }}"
                                    stroke="{{ $style['stroke'] }}" stroke-width="{{ $style['strokeWidth'] }}"
                                    stroke-dasharray="{{ $style['strokeDasharray'] }}" fill="none"
                                    class="relationship-line" />
                            @endif
                        @endforeach

                        <!-- 被相続人を表示 -->
                        @if ($deceasedPerson)
                            @php
                                $position = $this->getPersonPosition($deceasedPerson);
                            @endphp
                            <g>
                                <!-- 被相続人のラベル -->
                                <text x="{{ $position['x'] }}" y="{{ $position['y'] - 80 }}" text-anchor="left"
                                    class="text-sm font-bold fill-red-600">
                                    被相続人（亡くなった方）
                                </text>

                                <!-- 氏名 -->
                                <text x="{{ $position['x'] }}" y="{{ $position['y'] - 60 }}" text-anchor="left"
                                    class="text-sm font-medium fill-gray-900 cursor-pointer hover:opacity-80"
                                    onclick="selectPerson({{ $deceasedPerson->id }})">
                                    氏名: {{ $deceasedPerson->full_name }}
                                </text>

                                @php
                                    $yOffset = -45;
                                @endphp

                                <!-- 最後の本籍 -->
                                @if ($deceasedPerson->registered_domicile)
                                    <text x="{{ $position['x'] }}" y="{{ $position['y'] + $yOffset }}"
                                        text-anchor="left" class="text-xs fill-gray-600 cursor-pointer hover:opacity-80"
                                        onclick="selectPerson({{ $deceasedPerson->id }})">
                                        最後の本籍: {{ $deceasedPerson->registered_domicile }}
                                    </text>
                                    @php $yOffset += 15; @endphp
                                @endif

                                <!-- 最後の住所 -->
                                @if ($deceasedPerson->current_address)
                                    <text x="{{ $position['x'] }}" y="{{ $position['y'] + $yOffset }}"
                                        text-anchor="left" class="text-xs fill-gray-600 cursor-pointer hover:opacity-80"
                                        onclick="selectPerson({{ $deceasedPerson->id }})">
                                        最後の住所: {{ Str::limit($deceasedPerson->current_address, 40) }}
                                    </text>
                                    @php $yOffset += 15; @endphp
                                @endif

                                <!-- 申述書上の住所 -->
                                @if ($deceasedPerson->current_address)
                                    <text x="{{ $position['x'] }}" y="{{ $position['y'] + $yOffset }}"
                                        text-anchor="left" class="text-xs fill-gray-600 cursor-pointer hover:opacity-80"
                                        onclick="selectPerson({{ $deceasedPerson->id }})">
                                        申述書上の住所: {{ Str::limit($deceasedPerson->current_address, 40) }}
                                    </text>
                                    @php $yOffset += 15; @endphp
                                @endif

                                <!-- 出生 -->
                                @if ($deceasedPerson->birth_date)
                                    <text x="{{ $position['x'] }}" y="{{ $position['y'] + $yOffset }}"
                                        text-anchor="left" class="text-xs fill-gray-600 cursor-pointer hover:opacity-80"
                                        onclick="selectPerson({{ $deceasedPerson->id }})">
                                        出生: {{ $deceasedPerson->birth_date->format('Y年m月d日') }}
                                    </text>
                                    @php $yOffset += 15; @endphp
                                @endif

                                <!-- 死亡 -->
                                @if ($deceasedPerson->death_date)
                                    <text x="{{ $position['x'] }}" y="{{ $position['y'] + $yOffset }}"
                                        text-anchor="left" class="text-xs fill-gray-600 cursor-pointer hover:opacity-80"
                                        onclick="selectPerson({{ $deceasedPerson->id }})">
                                        死亡: {{ $deceasedPerson->death_date->format('Y年m月d日') }}
                                    </text>
                                @endif
                            </g>
                        @endif

                        <!-- 相続人を表示（生年月日順） -->
                        @foreach ($heirs as $index => $heir)
                            @php
                                $position = $this->getPersonPosition($heir);
                            @endphp
                            <g>
                                <!-- 相続人番号 -->
                                <text x="{{ $position['x'] }}" y="{{ $position['y'] - 60 }}" text-anchor="left"
                                    class="text-sm font-bold fill-blue-600">
                                    相続人{{ $index + 1 }}
                                </text>

                                @php
                                    $yOffset = -45;
                                @endphp

                                <!-- 続柄 -->
                                @if ($heir->relationship_to_deceased)
                                    <text x="{{ $position['x'] }}" y="{{ $position['y'] + $yOffset }}"
                                        text-anchor="left" class="text-xs fill-gray-600 cursor-pointer hover:opacity-80"
                                        onclick="selectPerson({{ $heir->id }})">
                                        続柄: {{ $heir->relationship_to_deceased }}
                                    </text>
                                    @php $yOffset += 15; @endphp
                                @endif

                                <!-- 氏名 -->
                                <text x="{{ $position['x'] }}" y="{{ $position['y'] + $yOffset }}" text-anchor="left"
                                    class="text-sm font-medium fill-gray-900 cursor-pointer hover:opacity-80"
                                    onclick="selectPerson({{ $heir->id }})">
                                    氏名: {{ $heir->full_name }}
                                </text>
                                @php $yOffset += 15; @endphp

                                <!-- 住所 -->
                                @if ($heir->current_address)
                                    <text x="{{ $position['x'] }}" y="{{ $position['y'] + $yOffset }}"
                                        text-anchor="left" class="text-xs fill-gray-600 cursor-pointer hover:opacity-80"
                                        onclick="selectPerson({{ $heir->id }})">
                                        住所: {{ Str::limit($heir->current_address, 40) }}
                                    </text>
                                    @php $yOffset += 15; @endphp
                                @endif

                                <!-- 出生 -->
                                @if ($heir->birth_date)
                                    <text x="{{ $position['x'] }}" y="{{ $position['y'] + $yOffset }}"
                                        text-anchor="left" class="text-xs fill-gray-600 cursor-pointer hover:opacity-80"
                                        onclick="selectPerson({{ $heir->id }})">
                                        出生: {{ $heir->birth_date->format('Y年m月d日') }}
                                    </text>
                                    @php $yOffset += 15; @endphp
                                @endif

                            </g>
                        @endforeach

                        <!-- 被相続人から相続人への関係線 -->
                        @if ($deceasedPerson)
                            @php
                                $deceasedPos = $this->getPersonPosition($deceasedPerson);
                                $heirs = $this->getHeirs();

                                // 配偶者とその他の相続人に分類
                                $spouses = $heirs->filter(function ($heir) {
                                    return $heir->relationship_to_deceased &&
                                        (str_contains($heir->relationship_to_deceased, '配偶者') ||
                                            str_contains($heir->relationship_to_deceased, '妻') ||
                                            str_contains($heir->relationship_to_deceased, '夫'));
                                });

                                $children = $heirs->filter(function ($heir) {
                                    return !$heir->relationship_to_deceased ||
                                        (!str_contains($heir->relationship_to_deceased, '配偶者') &&
                                            !str_contains($heir->relationship_to_deceased, '妻') &&
                                            !str_contains($heir->relationship_to_deceased, '夫'));
                                });
                            @endphp

                            <!-- 配偶者への二重縦線 -->
                            @foreach ($spouses as $spouse)
                                @php
                                    $spousePos = $this->getPersonPosition($spouse);
                                @endphp
                                <!-- 配偶者への二重縦線（夫婦間は二重線） -->
                                <line x1="{{ $deceasedPos['x'] + 100 }}" y1="{{ $deceasedPos['y'] + 50 }}"
                                    x2="{{ $spousePos['x'] + 100 }}" y2="{{ $spousePos['y'] - 50 }}" stroke="#374151"
                                    stroke-width="4" fill="none" />
                                <line x1="{{ $deceasedPos['x'] + 100 }}" y1="{{ $deceasedPos['y'] + 50 }}"
                                    x2="{{ $spousePos['x'] + 100 }}" y2="{{ $spousePos['y'] - 50 }}" stroke="#FFFFFF"
                                    stroke-width="2" fill="none" />
                            @endforeach

                            <!-- 子への関係線（実子と養子を分けて描画） -->
                            @if ($children->count() > 0)
                                @php
                                    // 実子と養子に分類
                                    $biologicalChildren = $children->filter(function ($child) {
                                        return !$child->relationship_to_deceased ||
                                            (!str_contains($child->relationship_to_deceased, '養子') &&
                                                !str_contains($child->relationship_to_deceased, '養女'));
                                    });

                                    $adoptedChildren = $children->filter(function ($child) {
                                        return $child->relationship_to_deceased &&
                                            (str_contains($child->relationship_to_deceased, '養子') ||
                                                str_contains($child->relationship_to_deceased, '養女'));
                                    });

                                    // 配偶者がいる場合、二重線の位置を計算
                                    if ($spouses->count() > 0) {
                                        $firstSpouse = $spouses->first();
                                        $spousePos = $this->getPersonPosition($firstSpouse);
                                        $spouseLineStart = $deceasedPos['y'] + 50; // 二重線の開始位置
                                        $spouseLineEnd = $spousePos['y'] - 50; // 二重線の終了位置
                                        $spouseLineLength = $spouseLineEnd - $spouseLineStart; // 二重線の長さ

                                        // 実子用の分岐点（上から5分の2の位置）
                                        $biologicalBranchY = $spouseLineStart + ($spouseLineLength * 2) / 5;

                                        // 養子用の分岐点（上から5分の3の位置）
                                        $adoptedBranchY = $spouseLineStart + ($spouseLineLength * 3) / 5;
                                    } else {
                                        // 配偶者がいない場合は被相続人の高さ
                                        $biologicalBranchY = $deceasedPos['y'];
                                        $adoptedBranchY = $deceasedPos['y'];
                                    }

                                    $branchX = $deceasedPos['x'] + 400; // 被相続人から右に400px（実子用）
                                    $adoptedBranchX = $deceasedPos['x'] + 350; // 被相続人から右に350px（養子用、左にずらす）
                                @endphp

                                <!-- 実子への関係線 -->
                                @if ($biologicalChildren->count() > 0)
                                    <!-- 被相続人から実子分岐点への水平線 -->
                                    <line x1="{{ $deceasedPos['x'] + 100 }}" y1="{{ $biologicalBranchY }}"
                                        x2="{{ $branchX }}" y2="{{ $biologicalBranchY }}" stroke="#374151"
                                        stroke-width="2" fill="none" />

                                    <!-- 各実子への枝分かれ線 -->
                                    @foreach ($biologicalChildren as $child)
                                        @php
                                            $childPos = $this->getPersonPosition($child);
                                            $childNameY = $childPos['y'] - 45; // 氏名のY座標
                                            $childX = $childPos['x'] - 50; // 子の左端
                                        @endphp

                                        <!-- 分岐点から子の高さへの縦線 -->
                                        <line x1="{{ $branchX }}" y1="{{ $biologicalBranchY }}"
                                            x2="{{ $branchX }}" y2="{{ $childNameY }}" stroke="#374151"
                                            stroke-width="2" fill="none" />

                                        <!-- 子の高さから子の名前の前への横線（実線） -->
                                        <line x1="{{ $branchX }}" y1="{{ $childNameY }}"
                                            x2="{{ $childX }}" y2="{{ $childNameY }}" stroke="#374151"
                                            stroke-width="2" fill="none" />
                                    @endforeach
                                @endif

                                <!-- 養子への関係線 -->
                                @if ($adoptedChildren->count() > 0)
                                    <!-- 被相続人から養子分岐点への水平線 -->
                                    <line x1="{{ $deceasedPos['x'] + 100 }}" y1="{{ $adoptedBranchY }}"
                                        x2="{{ $adoptedBranchX }}" y2="{{ $adoptedBranchY }}" stroke="#6B7280"
                                        stroke-width="2" stroke-dasharray="5,5" fill="none" />

                                    <!-- 各養子への枝分かれ線 -->
                                    @foreach ($adoptedChildren as $child)
                                        @php
                                            $childPos = $this->getPersonPosition($child);
                                            $childNameY = $childPos['y'] - 45; // 氏名のY座標
                                            $childX = $childPos['x'] - 50; // 子の左端
                                        @endphp

                                        <!-- 分岐点から子の高さへの縦線（点線） -->
                                        <line x1="{{ $adoptedBranchX }}" y1="{{ $adoptedBranchY }}"
                                            x2="{{ $adoptedBranchX }}" y2="{{ $childNameY }}" stroke="#6B7280"
                                            stroke-width="2" stroke-dasharray="5,5" fill="none" />

                                        <!-- 子の高さから子の名前の前への横線（点線） -->
                                        <line x1="{{ $adoptedBranchX }}" y1="{{ $childNameY }}"
                                            x2="{{ $childX }}" y2="{{ $childNameY }}" stroke="#6B7280"
                                            stroke-width="2" stroke-dasharray="5,5" fill="none" />
                                    @endforeach
                                @endif
                            @endif
                        @endif
                    </svg>
                </div>

                <!-- 被相続人の情報 -->
                @if ($deceasedPerson)
                    <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <h3 class="text-lg font-medium text-red-900 mb-4">被相続人</h3>
                        <div class="space-y-3">
                            <!-- 住所 -->
                            <div>
                                <p class="text-sm font-medium text-gray-700">住所</p>
                                <p class="text-sm text-gray-900">{{ $deceasedPerson->current_address ?? '未設定' }}</p>
                            </div>

                            <!-- 本籍 -->
                            <div>
                                <p class="text-sm font-medium text-gray-700">本籍</p>
                                <p class="text-sm text-gray-900">{{ $deceasedPerson->registered_domicile ?? '未設定' }}
                                </p>
                            </div>

                            <!-- 氏名 -->
                            <div>
                                <p class="text-sm font-medium text-gray-700">氏名</p>
                                <p class="text-sm text-gray-900">{{ $deceasedPerson->full_name }}</p>
                                <p class="text-sm text-gray-500">{{ $deceasedPerson->full_name_kana }}</p>
                            </div>

                            <!-- 出生年月日 -->
                            <div>
                                <p class="text-sm font-medium text-gray-700">出生年月日</p>
                                <p class="text-sm text-gray-900">
                                    {{ $deceasedPerson->birth_date ? $deceasedPerson->birth_date->format('Y年m月d日') : '未設定' }}
                                </p>
                            </div>

                            <!-- 死亡年月日 -->
                            <div>
                                <p class="text-sm font-medium text-gray-700">死亡年月日</p>
                                <p class="text-sm text-gray-900">
                                    {{ $deceasedPerson->death_date ? $deceasedPerson->death_date->format('Y年m月d日') : '未設定' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 相続人の情報 -->
                @if ($heirs->count() > 0)
                    <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <h3 class="text-lg font-medium text-green-900 mb-4">相続人一覧</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($heirs as $heir)
                                <div class="p-3 bg-white rounded border">
                                    <div class="flex items-center space-x-2 mb-3">
                                        <div
                                            class="w-3 h-3 rounded-full {{ $heir->is_alive ? 'bg-green-500' : 'bg-gray-400' }}">
                                        </div>
                                        <h4 class="text-sm font-medium text-gray-900">{{ $heir->full_name }}</h4>
                                    </div>

                                    <!-- 相続人の氏名 -->
                                    <div class="mb-2">
                                        <p class="text-xs font-medium text-gray-700">氏名</p>
                                        <p class="text-xs text-gray-900">{{ $heir->full_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $heir->full_name_kana }}</p>
                                    </div>

                                    <!-- 生年月日 -->
                                    <div class="mb-2">
                                        <p class="text-xs font-medium text-gray-700">生年月日</p>
                                        <p class="text-xs text-gray-900">
                                            {{ $heir->birth_date ? $heir->birth_date->format('Y年m月d日') : '未設定' }}
                                        </p>
                                    </div>

                                    <!-- 住所 -->
                                    <div class="mb-2">
                                        <p class="text-xs font-medium text-gray-700">住所</p>
                                        <p class="text-xs text-gray-900">{{ $heir->current_address ?? '未設定' }}</p>
                                    </div>

                                    @if ($heir->relationship_to_deceased)
                                        <div class="mt-2 pt-2 border-t border-gray-200">
                                            <p class="text-xs font-medium text-gray-700">続柄</p>
                                            <p class="text-xs text-blue-600">{{ $heir->relationship_to_deceased }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- 選択された人物の詳細情報 -->
                @if ($selectedPerson)
                    @php
                        $person = $people->firstWhere('id', $selectedPerson);
                    @endphp
                    @if ($person)
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900">{{ $person->full_name }}</h3>
                            <p class="text-sm text-gray-500">{{ $person->full_name_kana }}</p>
                            <div class="mt-2 flex space-x-2">
                                <button type="button" onclick="createRelationship({{ $person->id }})"
                                    class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                    関係を追加
                                </button>
                                <a href="{{ route('persons.edit', $person) }}"
                                    class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                    編集
                                </a>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    let currentZoom = 1;
    const svg = document.getElementById('inheritance-diagram-svg');

    function zoomIn() {
        currentZoom = Math.min(currentZoom * 1.2, 3);
        updateZoom();
    }

    function zoomOut() {
        currentZoom = Math.max(currentZoom / 1.2, 0.5);
        updateZoom();
    }

    function resetZoom() {
        currentZoom = 1;
        updateZoom();
    }

    function updateZoom() {
        svg.style.transform = `scale(${currentZoom})`;
    }

    function selectPerson(personId) {
        // 既存の選択をクリア
        document.querySelectorAll('g').forEach(group => {
            const texts = group.querySelectorAll('text');
            texts.forEach(text => {
                text.style.fill = '';
                text.style.fontWeight = '';
            });
        });

        // 新しい選択
        const selectedGroup = event.target.closest('g');
        const texts = selectedGroup.querySelectorAll('text');
        texts.forEach(text => {
            if (text.textContent.includes('被相続人')) {
                text.style.fill = '#DC2626';
                text.style.fontWeight = 'bold';
            } else if (text.textContent.includes('年') && text.textContent.includes('月')) {
                // 生年月日のテキスト
                text.style.fill = '#3B82F6';
                text.style.fontWeight = 'bold';
            } else if (text.textContent.includes('住所') || text.textContent.includes('住所未設定')) {
                // 住所のテキスト
                text.style.fill = '#3B82F6';
                text.style.fontWeight = 'bold';
            } else if (text.textContent.includes('配偶者') || text.textContent.includes('子') || text.textContent
                .includes('親') || text.textContent.includes('兄弟')) {
                // 続柄のテキスト
                text.style.fill = '#3B82F6';
                text.style.fontWeight = 'bold';
            } else {
                // 氏名のテキスト
                text.style.fill = '#3B82F6';
                text.style.fontWeight = 'bold';
            }
        });

        // Livewireに選択を通知
        @this.call('selectPerson', personId);
    }

    function createRelationship(personId) {
        // 関係性作成ページに遷移
        window.location.href = `{{ route('relationships.create', $familyTree) }}?person1=${personId}`;
    }
</script>
