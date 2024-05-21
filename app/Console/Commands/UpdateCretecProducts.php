<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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
        $this->info("========== 크레텍 상품 전처리 프로토콜 ==========");
        $this->info("크레텍 상품셋 엑셀 파일을 불러오는 중입니다.");
        $spreadsheet = IOFactory::load(public_path('assets/excel/cretec_products.csv'));
        $sheet = $spreadsheet->getSheet(0);
        $this->info("엑셀 파일로부터 품번들을 추출하는 중입니다.");
        $productHrefs = [];
        for ($i = 2; $i <= 71840; $i++) {
            $productNumber = $sheet->getCell('B' . $i)->getValue();
            $productHrefs[] = 'https://ctx.cretec.kr/CtxApp/ctx/selectItemDtlIfrm.do?itemCd=' . $productNumber;
        }
        $this->info("품절된 상품들을 DB 에 반영 중입니다.");
        $this->soldOutProducts($productHrefs);
    }
    protected function soldOutProducts($productHrefs)
    {
        try {
            $this->info('품절 대상 상품들의 상품 코드들을 추출하는 중입니다.');
            $soldOutProductCodes = DB::table('minewing_products')
                ->whereNotIn('productHref', $productHrefs)
                ->get('productCode')
                ->toArray();
            $codeStr = join(',', $soldOutProductCodes);
            $filePath=public_path('assets/txt/cretec/sold_out_product_codes.txt');
            file_put_contents(, $codeStr);
            DB::table('minewing_products')
                ->whereNotIn('productHref', $productHrefs)
                ->update([
                    'isActive' => 'N'
                ]);
            $this->info('품절된 상품들을 DB 에 성공적으로 반영했습니다.');
        } catch (\Exception $e) {
            $this->info('품절된 상품들을 DB 에 반영하는 과정에서 오류가 발생했습니다.');
            $this->error($e->getMessage());
        }
    }
}
