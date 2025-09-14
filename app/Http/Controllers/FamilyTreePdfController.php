<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FamilyTree;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FamilyTreePdfController extends Controller
{
    /**
     * 相続関係説明図をPDFで出力
     */
    public function exportInheritanceDiagram(FamilyTree $familyTree)
    {
        // 家系図のデータを取得
        $people = $familyTree->people()->orderBy('generation_level')->orderBy('display_order')->get();
        $relationships = $familyTree->relationships()->with(['person1', 'person2'])->get();
        $deceasedPerson = $familyTree->people()->where('id', $familyTree->deceased_person_id)->first();
        $heirs = $familyTree->people()->where('id', '!=', $familyTree->deceased_person_id)->orderBy('birth_date')->get();

        // PDF用のビューにデータを渡す
        $pdf = Pdf::loadView('pdf.inheritance-diagram', [
            'familyTree' => $familyTree,
            'people' => $people,
            'relationships' => $relationships,
            'deceasedPerson' => $deceasedPerson,
            'heirs' => $heirs,
        ]);

        // PDFの設定
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
        ]);

        // ファイル名を生成
        $filename = '相続関係説明図_' . $familyTree->title . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }
}
