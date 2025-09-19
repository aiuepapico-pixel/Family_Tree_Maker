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
            height: 500px;
            border: 1px solid #D1D5DB;
            background-color: white;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .diagram-image {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
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
        @if (isset($svgImageData))
            <img src="{{ $svgImageData }}" alt="相続関係説明図" class="diagram-image" />
        @else
            <div style="text-align: center; color: #6B7280; font-size: 14px;">
                図の生成中にエラーが発生しました。
            </div>
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
