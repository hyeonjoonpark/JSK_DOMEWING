<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\ProductImageController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class ImportCretecProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-cretec-products';

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
        ini_set('memory_limit', '-1');
        $filePath = storage_path('app/public/excel/cretec_products.csv');
        $spreadsheet = $this->loadCsv($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $this->extractDataFromSheet($sheet);
    }

    private function loadCsv($filePath)
    {
        $reader = new Csv();
        $reader->setDelimiter(',');
        $reader->setEnclosure('"');
        $reader->setInputEncoding('CP949');
        $reader->setSheetIndex(0);
        return $reader->load($filePath);
    }

    private function extractDataFromSheet($sheet)
    {
        $isFirstRow = true;
        foreach ($sheet->getRowIterator() as $index => $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }
            $product = $this->createProduct($sheet, $index);
            $this->insert($product);
        }
        echo "success";
    }
    private function insert($product)
    {
        DB::table('minewing_products')
            ->where('productHref', $product['productHref'])
            ->update($product);
    }
    private function createProduct($sheet, $index)
    {
        $originImageUrl = trim($sheet->getCell('I' . $index)->getValue());
        $pic = new ProductImageController();
        $productImage = $pic->index($originImageUrl, 'N')['return'];
        return [
            'productPrice' => (int)trim(ceil($sheet->getCell('O' . $index)->getValue() * $sheet->getCell('T' . $index)->getValue()),),
            'productImage' => $productImage,
            'productDetail' => $this->createProductDetail($sheet, $index, $productImage),
            'productHref' => trim('https://ctx.cretec.kr/CtxApp/ctx/selectItemDtlIfrm.do?itemCd=' . $sheet->getCell('B' . $index)->getValue())
        ];
    }
    private function createProductDetail($sheet, $index, $productImage)
    {
        $productDetailStr = $sheet->getCell('V' . $index)->getValue();
        $quantityNum = $sheet->getCell('T' . $index)->getValue();
        $quantityStr = $sheet->getCell('U' . $index)->getValue();
        $quantity = $quantityNum . $quantityStr;
        $productDetail = '
        <h1 style="color:red !important; font-weight:bold !important; font-size:3rem !important;">
            상품 규격: ' . $productDetailStr . '<br>
            출고 단위: ' . $quantity . '
        </h1><br>
        <br>
        <br>
        <center>
            <img src="https://www.sellwing.kr/images/CDN/cretec_header.jpg" style="width: 100% !important;"><br>
            <img src="' . $productImage . '" style="width: 100% !important;"><br>
            <h1 style="color:red !important; font-weight:bold !important; font-size:3rem !important;">
                상품 규격: ' . $productDetailStr . '<br>
                출고 단위: ' . $quantity . '
            </h1><br>
            <br>
            <br>
            <img src="https://www.sellwing.kr/images/CDN/cretec_footer.jpg" style="width: 100% !important;">
        </center>
        ';
        return $productDetail;
    }
}
