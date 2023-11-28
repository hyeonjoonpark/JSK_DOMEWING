<?php
namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TestController extends Controller
{
    public function uploadedProducts()
    {
        $spreadsheet = IOFactory::load(public_path('assets/excel/domeatoz_2010022418_20231128103724.xlsx'));
        $sheet = $spreadsheet->getSheet(0);
        $uploadedProducts = DB::table('collected_products')->where('isActive', 'Y')->where('id', '>=', '2')->where('id', '<=', '535')->get();
        foreach ($uploadedProducts as $product) {
            DB::table('uploaded_products')->insert([
                'productId' => $product->id,
                'userId' => 15,
                'newImageHref' => 'sample'
            ]);
        }
        return $uploadedProducts;
    }
    public function index()
    {
        $products = DB::table('collected_products')
            ->where('id', '>=', 1100)
            ->where('id', '<=', 4039)
            ->get();
        foreach ($products as $product) {
            $output = [];
            $parsedUrl = parse_url($product->productHref);
            $domain = $parsedUrl['host'];
            // 'www.' 제거
            $domain = str_replace('www.', '', $domain);
            if ($domain == 'dometopia.com') {
                $domain = 'dometopia.js';
            }
            if ($domain == 'babonara.co.kr') {
                $domain = 'babonara.js';
            }
            if ($domain == 'metaldiy.com') {
                $domain = 'metaldiy.js';
            }
            if ($domain == 'domeggook.com') {
                $domain = 'domemedb.js';
            }
            $script = public_path('js/price/' . $domain);
            $command = "node " . escapeshellarg($script) . " " . escapeshellarg($product->productHref);
            set_time_limit(0);
            exec($command, $output, $returnCode);
            if ($returnCode == 0 && isset($output[0])) {
                // 성공적으로 처리된 경우, 상품 정보를 추가하고 반복문을 종료
                $productPrice = json_decode($output[0], true);
                DB::table('collected_products')->where('id', $product->id)->update([
                    'productPrice' => $productPrice['productPrice'],
                    'updatedAt' => now()
                ]);
            }
            echo "Success";
        }
    }
}
