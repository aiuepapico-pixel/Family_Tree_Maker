<?php

use App\Models\FamilyTree;
use App\Models\Person;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public FamilyTree $familyTree;

    public function mount(FamilyTree $familyTree): void
    {
        $this->authorize('view', $familyTree);
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

            <!-- 家系図表示エリア -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="mb-4 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">家族構成員</h2>
                    <a href="{{ route('persons.wizard', $familyTree) }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        人物を追加
                    </a>
                </div>

                <!-- 世代ごとの人物リスト -->
                @foreach ($people->groupBy('generation_level') as $level => $generationPeople)
                    <div class="mb-8">
                        <h3 class="text-sm font-medium text-gray-500 mb-4">
                            {{ $level === 0 ? '基準世代' : ($level > 0 ? $level . '世代下' : abs($level) . '世代上') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($generationPeople as $person)
                                <div class="border rounded-lg p-4 @if (!$person->is_alive) bg-gray-50 @endif">
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
                                            <p>続柄: {{ $person->relationship_to_deceased }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

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
</div>
