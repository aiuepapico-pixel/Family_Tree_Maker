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
        ];
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
        $generationLevel = $person->generation_level;
        $displayOrder = $person->display_order;

        // 世代ごとのY座標
        $y = 100 + $generationLevel * 200;

        // 同一世代内でのX座標
        $x = 200 + $displayOrder * 300;

        return ['x' => $x, 'y' => $y];
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
                    <h2 class="text-lg font-medium text-gray-900">家系図</h2>
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

                <!-- SVG家系図 -->
                <div class="overflow-auto border rounded-lg" style="height: 600px;">
                    <svg id="family-tree-svg" width="100%" height="100%" viewBox="0 0 1200 800"
                        style="min-width: 1200px; min-height: 800px;">
                        <!-- 関係線 -->
                        @foreach ($relationships as $relationship)
                            <path d="{{ $this->getRelationshipPath($relationship) }}" stroke="#6B7280" stroke-width="2"
                                fill="none" class="relationship-line" />
                        @endforeach

                        <!-- 人物ノード -->
                        @foreach ($people as $person)
                            @php
                                $position = $this->getPersonPosition($person);
                            @endphp
                            <g>
                                <!-- 人物の円 -->
                                <circle cx="{{ $position['x'] }}" cy="{{ $position['y'] }}" r="30"
                                    fill="{{ $person->is_alive ? '#10B981' : '#6B7280' }}" stroke="#374151"
                                    stroke-width="2" class="person-node cursor-pointer hover:opacity-80"
                                    onclick="selectPerson({{ $person->id }})" />

                                <!-- 人物の名前 -->
                                <text x="{{ $position['x'] }}" y="{{ $position['y'] + 5 }}" text-anchor="middle"
                                    class="text-xs font-medium fill-white">
                                    {{ Str::limit($person->family_name, 4) }}
                                </text>
                                <text x="{{ $position['x'] }}" y="{{ $position['y'] + 15 }}" text-anchor="middle"
                                    class="text-xs font-medium fill-white">
                                    {{ Str::limit($person->given_name, 4) }}
                                </text>
                            </g>
                        @endforeach
                    </svg>
                </div>

                <!-- 選択された人物の情報 -->
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
    const svg = document.getElementById('family-tree-svg');

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
        document.querySelectorAll('.person-node').forEach(node => {
            node.style.stroke = '#374151';
            node.style.strokeWidth = '2';
        });

        // 新しい選択
        const selectedNode = event.target.closest('g').querySelector('.person-node');
        selectedNode.style.stroke = '#3B82F6';
        selectedNode.style.strokeWidth = '4';

        // Livewireに選択を通知
        @this.call('selectPerson', personId);
    }

    function createRelationship(personId) {
        // 関係性作成ページに遷移
        window.location.href = `{{ route('relationships.create', $familyTree) }}?person1=${personId}`;
    }
</script>
