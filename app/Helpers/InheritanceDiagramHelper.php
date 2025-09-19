<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\FamilyTree;
use App\Models\Person;
use App\Models\Relationship;
use Carbon\Carbon;

class InheritanceDiagramHelper
{
    /**
     * 和暦で日付をフォーマット
     */
    public static function formatJapaneseDate($date): string
    {
        if (!$date) {
            return '';
        }

        $year = $date->year;
        $month = $date->month;
        $day = $date->day;

        // 年号の判定
        if ($year >= 2019) {
            $era = '令和';
            $eraYear = $year - 2018;
        } elseif ($year >= 1989) {
            $era = '平成';
            $eraYear = $year - 1988;
        } elseif ($year >= 1926) {
            $era = '昭和';
            $eraYear = $year - 1925;
        } elseif ($year >= 1912) {
            $era = '大正';
            $eraYear = $year - 1911;
        } else {
            $era = '明治';
            $eraYear = $year - 1867;
        }

        return "{$era}{$eraYear}年{$month}月{$day}日";
    }

    /**
     * 人物の位置を取得（相続関係説明図用）
     */
    public static function getPersonPosition(Person $person, FamilyTree $familyTree): array
    {
        // 被相続人の場合は左側やや上に配置
        if ($person->id === $familyTree->deceased_person_id) {
            return [
                'x' => 100,
                'y' => 300,
                'type' => 'deceased',
            ];
        }

        // 相続人の場合
        $heirs = $familyTree->people()
            ->where('id', '!=', $familyTree->deceased_person_id)
            ->orderBy('birth_date')
            ->get();

        // 配偶者とその他の相続人に分類
        $spouses = $heirs->filter(function ($heir) use ($familyTree) {
            // relationship_to_deceasedフィールドでの判定
            if (
                $heir->relationship_to_deceased &&
                (str_contains($heir->relationship_to_deceased, '配偶者') ||
                    str_contains($heir->relationship_to_deceased, '妻') ||
                    str_contains($heir->relationship_to_deceased, '夫'))
            ) {
                return true;
            }

            // 関係性テーブルでの判定
            $deceasedPerson = $familyTree->people()->where('id', $familyTree->deceased_person_id)->first();
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

        // 配偶者の位置
        if ($spouses->contains('id', $person->id)) {
            $spouseIndex = $spouses->search(function ($heir) use ($person) {
                return $heir->id === $person->id;
            });
            return [
                'x' => 100,
                'y' => 450 + $spouseIndex * 100,
                'type' => 'spouse',
            ];
        }

        // その他の相続人の位置
        $heirIndex = $otherHeirs->search(function ($heir) use ($person) {
            return $heir->id === $person->id;
        });

        $childrenPerRow = 4; // 1行あたりの子の数
        $row = intval($heirIndex / $childrenPerRow);
        $col = $heirIndex % $childrenPerRow;

        return [
            'x' => 500 + $col * 200,
            'y' => 300 + $row * 150,
            'type' => 'heir',
        ];
    }

    /**
     * 関係線のパスを取得
     */
    public static function getRelationshipPath(Relationship $relationship, FamilyTree $familyTree): string
    {
        $person1Pos = self::getPersonPosition($relationship->person1, $familyTree);
        $person2Pos = self::getPersonPosition($relationship->person2, $familyTree);

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
    public static function getRelationshipStyle(Relationship $relationship): array
    {
        switch ($relationship->relationship_type) {
            case 'spouse':
                return [
                    'stroke' => '#000000',
                    'strokeWidth' => 3,
                    'strokeDasharray' => 'none',
                    'isDouble' => true,
                ];
            case 'parent_child':
                // 養子かどうかを判定
                $isAdopted = $relationship->person1->relationship_to_deceased &&
                    (str_contains($relationship->person1->relationship_to_deceased, '養子') ||
                        str_contains($relationship->person1->relationship_to_deceased, '養女'));

                if ($isAdopted) {
                    return [
                        'stroke' => '#6B7280',
                        'strokeWidth' => 2,
                        'strokeDasharray' => '5,5',
                        'isDouble' => false,
                    ];
                }

                return [
                    'stroke' => '#000000',
                    'strokeWidth' => 2,
                    'strokeDasharray' => 'none',
                    'isDouble' => false,
                ];
            case 'sibling':
                return [
                    'stroke' => '#000000',
                    'strokeWidth' => 2,
                    'strokeDasharray' => 'none',
                    'isDouble' => false,
                ];
            default:
                return [
                    'stroke' => '#000000',
                    'strokeWidth' => 2,
                    'strokeDasharray' => 'none',
                    'isDouble' => false,
                ];
        }
    }
}
