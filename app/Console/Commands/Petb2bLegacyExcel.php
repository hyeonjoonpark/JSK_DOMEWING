<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Petb2bLegacyExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:petb2b-legacy-excel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $spreadsheet = IOFactory::load(public_path('assets/excel/petb2b_legacy_products.xlsx'));
        $sheet = $spreadsheet->getSheet(0);
        $products = DB::table('minewing_products AS mp')
            ->join('ownerclan_category AS oc', 'oc.id', '=', 'mp.categoryID')
            ->where('mp.sellerID', 39)
            ->get();
        $index = 2;
        foreach ($products as $product) {
            $sheet->getCell('A' . $index)->setValue($product->categoryID);
            $sheet->getCell('B' . $index)->setValue($product->name);
            $sheet->getCell('C' . $index)->setValue($product->productKeywords);
            // 아래 코드에 오타가 있습니다: 'D' 열에 두 번 값을 입력하고 있습니다.
            $sheet->getCell('D' . $index)->setValue($product->productName);
            $sheet->getCell('E' . $index)->setValue($product->productHref); // 'E' 열로 수정했습니다.
            $index++;
        }

        // 파일을 다시 저장하는 코드를 추가합니다.
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save(public_path('assets/excel/petb2b_legacy_products.xlsx'));

        echo 'success';
    }
}
