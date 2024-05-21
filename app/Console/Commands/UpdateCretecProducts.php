<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class UpdateCretecProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-cretec-products';

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
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $this->info("========== 크레텍 상품 전처리 프로토콜 ==========");
        $this->info("크레텍 상품셋 엑셀 파일을 불러오는 중입니다.");
        $reader = new Csv();
        $reader->setDelimiter(',');
        $reader->setEnclosure('"');
        $reader->setInputEncoding('CP949');
        $reader->setSheetIndex(0);
        $spreadsheet = $reader->load(public_path('assets/excel/cretec_products.csv'));
        $sheet = $spreadsheet->getActiveSheet();
        $this->info("엑셀 파일로부터 품번들을 추출하는 중입니다.");
        $newProducts = [];
        for ($i = 2; $i <= $sheet->getHighestRow(); $i++) {
            $productNumber = $sheet->getCell('B' . $i)->getValue();
            $newProducts[] = [
                'index' => $i,
                'productHref' => "https://ctx.cretec.kr/CtxApp/ctx/selectItemDtlIfrm.do?itemCd=" . $productNumber
            ];
        }
        $this->info("품절 및 재입고된 상품들을 DB 에 반영 중입니다.");
        if (!$this->soldOutProducts($newProducts)) {
            return false;
        }
        $this->info("신상품들을 DB에 반영 중입니다.");
        $existingProductHrefs = DB::table('minewing_products')
            ->where('sellerID', 61)
            ->pluck('productHref')
            ->toArray();
        $newProductHrefs = array_column($newProducts, 'productHref');
        $newProductHrefs = array_diff($newProductHrefs, $existingProductHrefs);
        $newProducts = array_filter($newProducts, function ($product) use ($newProductHrefs) {
            return in_array($product['productHref'], $newProductHrefs);
        });
        $newProducts = array_values($newProducts);
        print_r($newProducts);
        $this->info("크레텍 상품 전처리 프로토콜 완료");
        return true;
    }
    protected function soldOutProducts($newProducts)
    {
        try {
            DB::table('minewing_products')
                ->where('sellerID', 61)
                ->update([
                    'isActive' => 'N'
                ]);
            $chunkedProducts = array_chunk($newProducts, 10000);
            foreach ($chunkedProducts as $products) {
                $productHrefs = array_column($products, 'productHref');
                DB::table('minewing_products')
                    ->where('sellerID', 61)
                    ->whereIn('productHref', $productHrefs)
                    ->update([
                        'isActive' => 'Y'
                    ]);
            }
            $this->info('품절 및 재입고된 상품들을 DB 에 성공적으로 반영했습니다.');
            return true;
        } catch (\Exception $e) {
            $this->info('품절 및 재입고된 상품들을 DB 에 반영하는 과정에서 오류가 발생했습니다.');
            Log::error($e->getMessage());
            return false;
        }
    }
}
