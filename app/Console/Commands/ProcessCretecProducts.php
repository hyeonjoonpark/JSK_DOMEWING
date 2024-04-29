<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\ProductImageController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;

class ProcessCretecProducts extends Command
{
    protected $signature = 'app:process-cretec-products';
    protected $description = 'Processes Cretec products from an Excel sheet';

    public function handle()
    {
        $this->info("Loading the excel file...");
        ini_set('memory_limit', '2048M'); // Set a specific memory limit

        $excelPath = storage_path("app/public/excel/ttr.xlsx");
        $sheet = $this->loadExcelSheet($excelPath);

        $this->info("Updating products...");
        $this->processProducts($sheet);

        $this->info("Processing completed successfully.");
    }

    private function loadExcelSheet($excelPath)
    {
        $reader = new ReaderXlsx();
        $reader->setReadDataOnly(true);
        $worksheet = $reader->load($excelPath);
        return $worksheet->getActiveSheet();
    }

    private function processProducts($sheet)
    {
        $pic = new ProductImageController();

        for ($i = 2; $i <= 5; $i++) {
            $productData = $this->extractProductData($sheet, $i);
            $productData['productImage'] = $pic->index($productData['productImageUrl'], 'N')['return'];
            $productData['productDetail'] = $this->generateProductDetailHtml($productData);

            $this->updateProductInDatabase($productData);
        }
    }

    private function extractProductData($sheet, $rowNumber)
    {
        return [
            'productHref' => "https://ctx.cretec.kr/CtxApp/ctx/selectItemDtlIfrm.do?itemCd=" . $sheet->getCell('B' . $rowNumber)->getValue(),
            'providedPrice' => $sheet->getCell('O' . $rowNumber)->getValue(),
            'quantity' => $sheet->getCell('T' . $rowNumber)->getValue(),
            'productPrice' => ceil($sheet->getCell('O' . $rowNumber)->getValue() * $sheet->getCell('T' . $rowNumber)->getValue()),
            'productDescription' => $sheet->getCell('V' . $rowNumber)->getValue(),
            'unit' => $sheet->getCell('U' . $rowNumber)->getValue(),
            'productImageUrl' => $sheet->getCell('I' . $rowNumber)->getValue()
        ];
    }

    private function generateProductDetailHtml($data)
    {
        return '
        <h1 style="color:red !important; font-weight:bold !important; font-size:3rem !important;">
            상품 규격: ' . $data['productDescription'] . '<br>
            출고 단위: ' . $data['quantity'] . $data['unit'] . '
        </h1><br>
        <br>
        <br>
        <center>
            <img src="https://www.sellwing.kr/images/CDN/cretec_header.jpg" style="width: 100% !important;"><br>
            <img src="' . $data['productImage'] . '" style="width: 100% !important;"><br>
            <h1 style="color:red !important; font-weight:bold !important; font-size:3rem !important;">
                상품 규격: ' . $data['productDescription'] . '<br>
                출고 단위: ' . $data['quantity'] . $data['unit'] . '
            </h1><br>
            <br>
            <br>
            <img src="https://www.sellwing.kr/images/CDN/cretec_footer.jpg" style="width: 100% !important;">
        </center>
        ';
    }

    private function updateProductInDatabase($product)
    {
        DB::table('minewing_products')
            ->where('productHref', $product['productHref'])
            ->update([
                'productPrice' => $product['productPrice'],
                'productImage' => $product['productImage'],
                'productDetail' => $product['productDetail']
            ]);
    }
}
