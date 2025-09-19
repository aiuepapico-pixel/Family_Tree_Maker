<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\FamilyTree;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SvgToImageHelper
{
    /**
     * SVGを画像に変換してBase64エンコードされたデータURIを返す
     */
    public static function convertSvgToImage(string $svgContent, int $width = 1400, int $height = 800): string
    {
        // SVGの内容を取得
        $svg = self::prepareSvgForConversion($svgContent, $width, $height);

        // 一時ファイルにSVGを保存
        $tempSvgPath = storage_path('app/temp/' . Str::random(10) . '.svg');
        $tempPngPath = storage_path('app/temp/' . Str::random(10) . '.png');

        // ディレクトリが存在しない場合は作成
        if (!file_exists(dirname($tempSvgPath))) {
            mkdir(dirname($tempSvgPath), 0755, true);
        }

        file_put_contents($tempSvgPath, $svg);

        try {
            // ImageMagickを使用してSVGをPNGに変換
            $command = sprintf(
                'convert -background white -density 300 "%s" -resize %dx%d "%s"',
                $tempSvgPath,
                $width,
                $height,
                $tempPngPath
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($tempPngPath)) {
                // PNGファイルをBase64エンコード
                $imageData = file_get_contents($tempPngPath);
                $base64 = base64_encode($imageData);

                // 一時ファイルを削除
                unlink($tempSvgPath);
                unlink($tempPngPath);

                return 'data:image/png;base64,' . $base64;
            } else {
                // ImageMagickが利用できない場合は、SVGを直接Base64エンコード
                $base64 = base64_encode($svg);
                unlink($tempSvgPath);

                return 'data:image/svg+xml;base64,' . $base64;
            }
        } catch (\Exception $e) {
            // エラーの場合はSVGを直接Base64エンコード
            $base64 = base64_encode($svg);
            if (file_exists($tempSvgPath)) {
                unlink($tempSvgPath);
            }

            return 'data:image/svg+xml;base64,' . $base64;
        }
    }

    /**
     * SVGを変換用に準備する
     */
    private static function prepareSvgForConversion(string $svgContent, int $width, int $height): string
    {
        // SVGタグを適切に設定
        $svg = str_replace(
            '<svg',
            sprintf('<svg width="%d" height="%d" xmlns="http://www.w3.org/2000/svg"', $width, $height),
            $svgContent
        );

        // フォントファミリーを設定
        $svg = str_replace(
            'class="text-lg font-bold fill-gray-900"',
            'style="font-size: 18px; font-weight: bold; fill: #111827; font-family: DejaVu Sans, sans-serif;"',
            $svg
        );

        $svg = str_replace(
            'class="text-xs fill-gray-600"',
            'style="font-size: 12px; fill: #6B7280; font-family: DejaVu Sans, sans-serif;"',
            $svg
        );

        $svg = str_replace(
            'class="text-sm font-medium fill-gray-900"',
            'style="font-size: 14px; font-weight: 500; fill: #111827; font-family: DejaVu Sans, sans-serif;"',
            $svg
        );

        return $svg;
    }

    /**
     * 家系図のSVGコンテンツを生成
     */
    public static function generateFamilyTreeSvg(FamilyTree $familyTree, $people, $relationships, $deceasedPerson, $heirs): string
    {
        // 相続人の数に応じてSVGの高さを調整
        $childrenCount = $heirs->count();
        $svgHeight = 800; // デフォルトの高さ

        if ($childrenCount > 8) {
            $svgHeight = 1400; // 子どもが多い場合は高さを増やす
        } elseif ($childrenCount > 6) {
            $svgHeight = 1200;
        } elseif ($childrenCount > 4) {
            $svgHeight = 1100;
        }

        $svg = sprintf(
            '<svg id="inheritance-diagram-svg" width="100%%" height="100%%" viewBox="0 0 1400 %d" style="min-width: 1400px; min-height: %dpx;">',
            $svgHeight,
            $svgHeight
        );

        // 表題
        $svg .= sprintf(
            '<text x="100" y="50" text-anchor="left" style="font-size: 18px; font-weight: bold; fill: #111827; font-family: DejaVu Sans, sans-serif;">被相続人 %s 相続関係説明図</text>',
            $deceasedPerson ? $deceasedPerson->full_name : '未設定'
        );

        // 被相続人の住所情報
        if ($deceasedPerson) {
            $addressY = 80;

            if ($deceasedPerson->current_address) {
                $svg .= sprintf(
                    '<text x="100" y="%d" text-anchor="left" style="font-size: 12px; fill: #6B7280; font-family: DejaVu Sans, sans-serif;">最後の住所　　　　　%s</text>',
                    $addressY,
                    Str::limit($deceasedPerson->current_address, 40)
                );
                $addressY += 15;
            }

            if ($deceasedPerson->registered_domicile) {
                $svg .= sprintf(
                    '<text x="100" y="%d" text-anchor="left" style="font-size: 12px; fill: #6B7280; font-family: DejaVu Sans, sans-serif;">最後の本籍　　　　　%s</text>',
                    $addressY,
                    $deceasedPerson->registered_domicile
                );
                $addressY += 15;
            }

            if ($deceasedPerson->current_address) {
                $svg .= sprintf(
                    '<text x="100" y="%d" text-anchor="left" style="font-size: 12px; fill: #6B7280; font-family: DejaVu Sans, sans-serif;">登記記録上の住所　　%s</text>',
                    $addressY,
                    Str::limit($deceasedPerson->current_address, 40)
                );
            }
        }

        // 関係線
        foreach ($relationships as $relationship) {
            $style = InheritanceDiagramHelper::getRelationshipStyle($relationship);
            $path = InheritanceDiagramHelper::getRelationshipPath($relationship, $familyTree);

            if ($style['isDouble']) {
                // 二重線（配偶者関係）
                $svg .= sprintf(
                    '<path d="%s" stroke="%s" stroke-width="%s" stroke-dasharray="%s" fill="none" />',
                    $path,
                    $style['stroke'],
                    $style['strokeWidth'],
                    $style['strokeDasharray']
                );
                $svg .= sprintf(
                    '<path d="%s" stroke="%s" stroke-width="1" stroke-dasharray="%s" fill="none" style="transform: translateY(2px);" />',
                    $path,
                    $style['stroke'],
                    $style['strokeDasharray']
                );
            } else {
                // 通常の線
                $svg .= sprintf(
                    '<path d="%s" stroke="%s" stroke-width="%s" stroke-dasharray="%s" fill="none" />',
                    $path,
                    $style['stroke'],
                    $style['strokeWidth'],
                    $style['strokeDasharray']
                );
            }
        }

        // 被相続人を表示
        if ($deceasedPerson) {
            $position = InheritanceDiagramHelper::getPersonPosition($deceasedPerson, $familyTree);
            $yOffset = -60;

            if ($deceasedPerson->birth_date) {
                $svg .= sprintf(
                    '<text x="%d" y="%d" text-anchor="left" style="font-size: 12px; fill: #6B7280; font-family: DejaVu Sans, sans-serif;">出生 %s</text>',
                    $position['x'],
                    $position['y'] + $yOffset,
                    InheritanceDiagramHelper::formatJapaneseDate($deceasedPerson->birth_date)
                );
                $yOffset += 15;
            }

            if ($deceasedPerson->death_date) {
                $svg .= sprintf(
                    '<text x="%d" y="%d" text-anchor="left" style="font-size: 12px; fill: #6B7280; font-family: DejaVu Sans, sans-serif;">死亡 %s</text>',
                    $position['x'],
                    $position['y'] + $yOffset,
                    InheritanceDiagramHelper::formatJapaneseDate($deceasedPerson->death_date)
                );
                $yOffset += 15;
            }

            $svg .= sprintf(
                '<text x="%d" y="%d" text-anchor="left" style="font-size: 12px; fill: #6B7280; font-family: DejaVu Sans, sans-serif;">(被相続人)</text>',
                $position['x'],
                $position['y'] + $yOffset
            );
            $yOffset += 15;

            $svg .= sprintf(
                '<text x="%d" y="%d" text-anchor="left" style="font-size: 14px; font-weight: 500; fill: #111827; font-family: DejaVu Sans, sans-serif;">%s</text>',
                $position['x'],
                $position['y'] + $yOffset,
                $deceasedPerson->full_name
            );
        }

        // 相続人を表示
        $spouses = $heirs->filter(function ($heir) use ($deceasedPerson, $familyTree) {
            if (
                $heir->relationship_to_deceased &&
                (str_contains($heir->relationship_to_deceased, '配偶者') ||
                    str_contains($heir->relationship_to_deceased, '妻') ||
                    str_contains($heir->relationship_to_deceased, '夫'))
            ) {
                return true;
            }

            if ($deceasedPerson) {
                $spouseRelationship = $familyTree
                    ->relationships()
                    ->where(function ($query) use ($heir, $deceasedPerson) {
                        $query
                            ->where('person1_id', $heir->id)
                            ->where('person2_id', $deceasedPerson->id)
                            ->where('relationship_type', 'spouse');
                    })
                    ->orWhere(function ($query) use ($heir, $deceasedPerson) {
                        $query
                            ->where('person1_id', $deceasedPerson->id)
                            ->where('person2_id', $heir->id)
                            ->where('relationship_type', 'spouse');
                    })
                    ->first();

                return $spouseRelationship !== null;
            }

            return false;
        });

        $otherHeirs = $heirs->filter(function ($heir) use ($spouses) {
            return !$spouses->contains('id', $heir->id);
        });

        // 配偶者を表示
        foreach ($spouses as $heir) {
            $position = InheritanceDiagramHelper::getPersonPosition($heir, $familyTree);
            $yOffset = -45;

            if ($heir->current_address) {
                $svg .= sprintf(
                    '<text x="%d" y="%d" text-anchor="left" style="font-size: 12px; fill: #6B7280; font-family: DejaVu Sans, sans-serif;">住所 %s</text>',
                    $position['x'],
                    $position['y'] + $yOffset,
                    Str::limit($heir->current_address, 40)
                );
                $yOffset += 15;
            }

            if ($heir->birth_date) {
                $svg .= sprintf(
                    '<text x="%d" y="%d" text-anchor="left" style="font-size: 12px; fill: #6B7280; font-family: DejaVu Sans, sans-serif;">出生 %s</text>',
                    $position['x'],
                    $position['y'] + $yOffset,
                    InheritanceDiagramHelper::formatJapaneseDate($heir->birth_date)
                );
                $yOffset += 15;
            }

            if ($heir->death_date) {
                $svg .= sprintf(
                    '<text x="%d" y="%d" text-anchor="left" style="font-size: 12px; fill: #6B7280; font-family: DejaVu Sans, sans-serif;">死亡 %s</text>',
                    $position['x'],
                    $position['y'] + $yOffset,
                    InheritanceDiagramHelper::formatJapaneseDate($heir->death_date)
                );
                $yOffset += 15;
            }

            $svg .= sprintf(
                '<text x="%d" y="%d" text-anchor="left" style="font-size: 12px; fill: #6B7280; font-family: DejaVu Sans, sans-serif;">(相続人)</text>',
                $position['x'],
                $position['y'] + $yOffset
            );
            $yOffset += 15;

            $svg .= sprintf(
                '<text x="%d" y="%d" text-anchor="left" style="font-size: 14px; font-weight: 500; fill: #111827; font-family: DejaVu Sans, sans-serif;">%s</text>',
                $position['x'],
                $position['y'] + $yOffset,
                $heir->full_name
            );
        }

        // その他の相続人を表示
        foreach ($otherHeirs as $heir) {
            $position = InheritanceDiagramHelper::getPersonPosition($heir, $familyTree);
            $yOffset = -45;

            if ($heir->current_address) {
                $svg .= sprintf(
                    '<text x="%d" y="%d" text-anchor="left" style="font-size: 12px; fill: #6B7280; font-family: DejaVu Sans, sans-serif;">住所 %s</text>',
                    $position['x'],
                    $position['y'] + $yOffset,
                    Str::limit($heir->current_address, 40)
                );
                $yOffset += 15;
            }

            if ($heir->birth_date) {
                $svg .= sprintf(
                    '<text x="%d" y="%d" text-anchor="left" style="font-size: 12px; fill: #6B7280; font-family: DejaVu Sans, sans-serif;">出生 %s</text>',
                    $position['x'],
                    $position['y'] + $yOffset,
                    InheritanceDiagramHelper::formatJapaneseDate($heir->birth_date)
                );
                $yOffset += 15;
            }

            if ($heir->death_date) {
                $svg .= sprintf(
                    '<text x="%d" y="%d" text-anchor="left" style="font-size: 12px; fill: #6B7280; font-family: DejaVu Sans, sans-serif;">死亡 %s</text>',
                    $position['x'],
                    $position['y'] + $yOffset,
                    InheritanceDiagramHelper::formatJapaneseDate($heir->death_date)
                );
                $yOffset += 15;
            }

            $svg .= sprintf(
                '<text x="%d" y="%d" text-anchor="left" style="font-size: 12px; fill: #6B7280; font-family: DejaVu Sans, sans-serif;">(相続人)</text>',
                $position['x'],
                $position['y'] + $yOffset
            );
            $yOffset += 15;

            $svg .= sprintf(
                '<text x="%d" y="%d" text-anchor="left" style="font-size: 14px; font-weight: 500; fill: #111827; font-family: DejaVu Sans, sans-serif;">%s</text>',
                $position['x'],
                $position['y'] + $yOffset,
                $heir->full_name
            );
        }

        // 被相続人から相続人への関係線
        if ($deceasedPerson) {
            $deceasedPos = InheritanceDiagramHelper::getPersonPosition($deceasedPerson, $familyTree);

            // 配偶者への二重縦線
            foreach ($spouses as $spouse) {
                $spousePos = InheritanceDiagramHelper::getPersonPosition($spouse, $familyTree);

                $svg .= sprintf(
                    '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#374151" stroke-width="4" fill="none" />',
                    $deceasedPos['x'] + 50,
                    $deceasedPos['y'] + 25,
                    $spousePos['x'] + 50,
                    $spousePos['y'] - 75
                );
                $svg .= sprintf(
                    '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#FFFFFF" stroke-width="2" fill="none" />',
                    $deceasedPos['x'] + 50,
                    $deceasedPos['y'] + 25,
                    $spousePos['x'] + 50,
                    $spousePos['y'] - 75
                );
            }

            // 子への関係線
            $children = $heirs->filter(function ($heir) {
                return !$heir->relationship_to_deceased ||
                    (!str_contains($heir->relationship_to_deceased, '配偶者') &&
                        !str_contains($heir->relationship_to_deceased, '妻') &&
                        !str_contains($heir->relationship_to_deceased, '夫'));
            });

            if ($children->count() > 0) {
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
                    $spousePos = InheritanceDiagramHelper::getPersonPosition($firstSpouse, $familyTree);
                    $spouseLineStart = $deceasedPos['y'] + 25;
                    $spouseLineEnd = $spousePos['y'] - 75;
                    $spouseLineLength = $spouseLineEnd - $spouseLineStart;

                    $biologicalBranchY = $spouseLineStart + ($spouseLineLength * 2) / 5;
                    $adoptedBranchY = $spouseLineStart + ($spouseLineLength * 3) / 5;
                } else {
                    $biologicalBranchY = $deceasedPos['y'];
                    $adoptedBranchY = $deceasedPos['y'];
                }

                $branchX = $deceasedPos['x'] + 400;
                $adoptedBranchX = $deceasedPos['x'] + 375;

                // 実子への関係線
                if ($biologicalChildren->count() > 0) {
                    $svg .= sprintf(
                        '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#374151" stroke-width="2" fill="none" />',
                        $deceasedPos['x'] + 50,
                        $biologicalBranchY,
                        $branchX,
                        $biologicalBranchY
                    );

                    foreach ($biologicalChildren as $child) {
                        $childPos = InheritanceDiagramHelper::getPersonPosition($child, $familyTree);
                        $childNameY = $childPos['y'] - 45;
                        $childX = $childPos['x'] - 50;

                        $svg .= sprintf(
                            '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#374151" stroke-width="2" fill="none" />',
                            $branchX,
                            $biologicalBranchY,
                            $branchX,
                            $childNameY
                        );

                        $svg .= sprintf(
                            '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#374151" stroke-width="2" fill="none" />',
                            $branchX,
                            $childNameY,
                            $childX,
                            $childNameY
                        );
                    }
                }

                // 養子への関係線
                if ($adoptedChildren->count() > 0) {
                    $svg .= sprintf(
                        '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#6B7280" stroke-width="2" stroke-dasharray="5,5" fill="none" />',
                        $deceasedPos['x'] + 50,
                        $adoptedBranchY,
                        $adoptedBranchX,
                        $adoptedBranchY
                    );

                    foreach ($adoptedChildren as $child) {
                        $childPos = InheritanceDiagramHelper::getPersonPosition($child, $familyTree);
                        $childNameY = $childPos['y'] - 45;
                        $childX = $childPos['x'] - 50;

                        $svg .= sprintf(
                            '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#6B7280" stroke-width="2" stroke-dasharray="5,5" fill="none" />',
                            $adoptedBranchX,
                            $adoptedBranchY,
                            $adoptedBranchX,
                            $childNameY
                        );

                        $svg .= sprintf(
                            '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#6B7280" stroke-width="2" stroke-dasharray="5,5" fill="none" />',
                            $adoptedBranchX,
                            $childNameY,
                            $childX,
                            $childNameY
                        );
                    }
                }
            }
        }

        $svg .= '</svg>';

        return $svg;
    }
}
