<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpParser\Node\Expr\Cast\Object_;

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
        $this->info("========== 크레텍 상품 전처리 프로토콜 ==========");
        $this->info("크레텍 상품셋 엑셀 파일을 불러오는 중입니다.");
        $reader = IOFactory::createReader('Csv');
        $spreadsheet = $reader->load(public_path('assets/excel/cretec_products.csv'));
        $sheet = $spreadsheet->getActiveSheet();
        $this->info("엑셀 파일로부터 품번들을 추출하는 중입니다.");
        $productHrefs = [];
        for ($i = 2; $i <= 71840; $i++) {
            $productNumber = $sheet->getCell('B' . $i)->getValue();
            echo $productNumber . "\n";
            $productHrefs[] = 'https://ctx.cretec.kr/CtxApp/ctx/selectItemDtlIfrm.do?itemCd=' . $productNumber;
        }
        $this->info("품절된 상품들을 DB 에 반영 중입니다.");
        if (!$this->soldOutProducts($productHrefs)) {
            return false;
        }
        $this->info("크레텍 상품 전처리 프로토콜 완료");
        return true;
    }
    protected function soldOutProducts($productHrefs)
    {
        try {
            $this->info('품절 대상 상품들의 상품 코드들을 추출하는 중입니다.');
            $chunkedProductHrefs = array_chunk($productHrefs, 1000);
            $productCodes = [];
            foreach ($chunkedProductHrefs as $hrefs) {
                $tempProductCodes = DB::table('minewing_products')
                    ->where('sellerID', 61)
                    ->whereNotIn('productHref', $productHrefs)
                    ->pluck('productCode')
                    ->toArray();
                $productCodes = array_merge($productCodes, $tempProductCodes);
                DB::table('minewing_products')
                    ->where('sellerID', 61)
                    ->whereNotIn('productHref', $productHrefs)
                    ->update([
                        'isActive' => 'N'
                    ]);
            }
            $this->info('품절된 상품들을 DB 에 성공적으로 반영했습니다.');
            $codeStr = join(',', $productCodes);
            $filePath = public_path('assets/txt/cretec/sold_out_product_codes.txt');
            file_put_contents($filePath, $codeStr);
            $this->info('품절 상품 코드들을 파일에 저장하였습니다. 파일 경로: ' . $filePath);
            return true;
        } catch (\Exception $e) {
            $this->info('품절된 상품들을 DB 에 반영하는 과정에서 오류가 발생했습니다.');
            Log::error($e->getMessage());
            return false;
        }
    }
}
