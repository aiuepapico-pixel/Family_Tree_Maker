<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>相続関係説明図 - {{ $familyTree->title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: white;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #374151;
            padding-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 16px;
            color: #6B7280;
        }

        .diagram-container {
            width: 100%;
            height: 600px;
            border: 1px solid #D1D5DB;
            background-color: white;
            position: relative;
            overflow: hidden;
        }

        .person-info {
            position: absolute;
            background-color: white;
            border: 1px solid #D1D5DB;
            border-radius: 4px;
            padding: 8px;
            font-size: 12px;
            min-width: 120px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .deceased-person {
            background-color: #FEF2F2;
            border-color: #FCA5A5;
        }

        .heir-person {
            background-color: #F0FDF4;
            border-color: #86EFAC;
        }

        .person-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .person-details {
            font-size: 10px;
            color: #6B7280;
            line-height: 1.3;
        }

        .relationship-line {
            position: absolute;
            background-color: #374151;
            z-index: 1;
        }

        .spouse-line {
            height: 3px;
        }

        .parent-child-line {
            height: 2px;
        }

        .adopted-line {
            height: 2px;
            border-top: 2px dashed #6B7280;
            background-color: transparent;
        }

        .legend {
            margin-top: 20px;
            padding: 15px;
            background-color: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 4px;
        }

        .legend-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #374151;
        }

        .legend-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .legend-line {
            display: inline-block;
            width: 30px;
            height: 2px;
            background-color: #374151;
            margin-right: 8px;
            vertical-align: middle;
        }

        .legend-double-line {
            display: inline-block;
            width: 30px;
            height: 3px;
            background-color: #374151;
            margin-right: 8px;
            vertical-align: middle;
        }

        .legend-dashed-line {
            display: inline-block;
            width: 30px;
            height: 2px;
            border-top: 2px dashed #6B7280;
            margin-right: 8px;
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">被相続人 {{ $deceasedPerson ? $deceasedPerson->full_name : '未設定' }} 相続関係説明図</div>
        <div class="subtitle">{{ $familyTree->title }} - 総人数: {{ $people->count() }}人 | 相続人: {{ $heirs->count() }}人</div>
    </div>

    <div class="diagram-container">
        @php
            // 被相続人の位置
            $deceasedX = 100;
            $deceasedY = 200;

            // 相続人の位置計算
            $heirPositions = [];
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

            // 配偶者の位置
            foreach ($spouses as $index => $spouse) {
                $heirPositions[$spouse->id] = [
                    'x' => $deceasedX,
                    'y' => $deceasedY + 150 + $index * 100,
                    'type' => 'spouse',
                ];
            }

            // 子の位置
            $childrenCount = $children->count();
            $childrenStartX = $deceasedX + 200;
            $childrenStartY = $deceasedY;

            foreach ($children as $index => $child) {
                $heirPositions[$child->id] = [
                    'x' => $childrenStartX + $index * 150,
                    'y' => $childrenStartY + ($index % 2) * 100,
                    'type' => 'child',
                ];
            }
        @endphp

        <!-- 被相続人 -->
        @if ($deceasedPerson)
            <div class="person-info deceased-person" style="left: {{ $deceasedX }}px; top: {{ $deceasedY }}px;">
                <div class="person-name">{{ $deceasedPerson->full_name }}</div>
                <div class="person-details">
                    @if ($deceasedPerson->birth_date)
                        出生 {{ $deceasedPerson->birth_date->format('Y年m月d日') }}<br>
                    @endif
                    @if ($deceasedPerson->death_date)
                        死亡 {{ $deceasedPerson->death_date->format('Y年m月d日') }}<br>
                    @endif
                    @if ($deceasedPerson->current_address)
                        住所 {{ Str::limit($deceasedPerson->current_address, 30) }}<br>
                    @endif
                    @if ($deceasedPerson->registered_domicile)
                        本籍 {{ Str::limit($deceasedPerson->registered_domicile, 30) }}<br>
                    @endif
                    <strong>(被相続人)</strong>
                </div>
            </div>
        @endif

        <!-- 相続人 -->
        @foreach ($heirs as $heir)
            @if (isset($heirPositions[$heir->id]))
                @php
                    $pos = $heirPositions[$heir->id];
                @endphp
                <div class="person-info heir-person" style="left: {{ $pos['x'] }}px; top: {{ $pos['y'] }}px;">
                    <div class="person-name">{{ $heir->full_name }}</div>
                    <div class="person-details">
                        @if ($heir->birth_date)
                            出生 {{ $heir->birth_date->format('Y年m月d日') }}<br>
                        @endif
                        @if ($heir->current_address)
                            住所 {{ Str::limit($heir->current_address, 30) }}<br>
                        @endif
                        @if ($heir->relationship_to_deceased)
                            <strong>{{ $heir->relationship_to_deceased }}</strong><br>
                        @endif
                        <strong>(相続人)</strong>
                    </div>
                </div>
            @endif
        @endforeach

        <!-- 関係線 -->
        @if ($deceasedPerson)
            <!-- 配偶者への関係線 -->
            @foreach ($spouses as $spouse)
                @if (isset($heirPositions[$spouse->id]))
                    @php
                        $spousePos = $heirPositions[$spouse->id];
                    @endphp
                    <!-- 二重線（配偶者関係） -->
                    <div class="relationship-line spouse-line"
                        style="left: {{ $deceasedX + 60 }}px; top: {{ $deceasedY + 40 }}px; 
                                width: {{ abs($spousePos['x'] - $deceasedX) }}px; 
                                transform: rotate({{ (atan2($spousePos['y'] - $deceasedY, $spousePos['x'] - $deceasedX) * 180) / pi() }}deg);">
                    </div>
                @endif
            @endforeach

            <!-- 子への関係線 -->
            @if ($children->count() > 0)
                @php
                    $branchX = $deceasedX + 100;
                    $branchY = $deceasedY + 40;
                @endphp

                <!-- 被相続人から分岐点への線 -->
                <div class="relationship-line parent-child-line"
                    style="left: {{ $deceasedX + 60 }}px; top: {{ $branchY }}px; width: 40px;">
                </div>

                <!-- 各子への枝分かれ線 -->
                @foreach ($children as $child)
                    @if (isset($heirPositions[$child->id]))
                        @php
                            $childPos = $heirPositions[$child->id];
                            $isAdopted =
                                $child->relationship_to_deceased &&
                                (str_contains($child->relationship_to_deceased, '養子') ||
                                    str_contains($child->relationship_to_deceased, '養女'));
                        @endphp

                        <!-- 分岐点から子への線 -->
                        <div class="relationship-line {{ $isAdopted ? 'adopted-line' : 'parent-child-line' }}"
                            style="left: {{ $branchX }}px; top: {{ $branchY }}px; 
                                    width: {{ abs($childPos['x'] - $branchX) }}px; 
                                    transform: rotate({{ (atan2($childPos['y'] - $branchY, $childPos['x'] - $branchX) * 180) / pi() }}deg);">
                        </div>
                    @endif
                @endforeach
            @endif
        @endif
    </div>

    <!-- 凡例 -->
    <div class="legend">
        <div class="legend-title">凡例</div>
        <div class="legend-item">
            <span class="legend-double-line"></span>配偶者関係（二重線）
        </div>
        <div class="legend-item">
            <span class="legend-line"></span>親子関係（実線）
        </div>
        <div class="legend-item">
            <span class="legend-dashed-line"></span>養子関係（点線）
        </div>
    </div>

    <!-- フッター情報 -->
    <div style="margin-top: 20px; text-align: center; font-size: 10px; color: #6B7280;">
        出力日時: {{ now()->format('Y年m月d日 H:i') }} | 家系図ID: {{ $familyTree->id }}
    </div>
</body>

</html>
