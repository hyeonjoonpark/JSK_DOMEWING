<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\FormController;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use DOMDocument;
use DOMXPath;

class TestController extends Controller
{
    public function index()
    {
        $targetProducts = DB::table('uploaded_products as up')
            ->join('collected_products as cp', 'cp.id', '=', 'up.productId')
            ->whereBetween('up.id', [1002, 1074])
            ->select('*', 'up.id as up_id')
            ->get();
        foreach ($targetProducts as $product) {
            $fc = new FormController();
            $newProductName = $fc->editProductName($product->productName);
            DB::table('uploaded_products as up')
                ->where('up.id', $product->up_id)
                ->update([
                    'up.newProductName' => $newProductName
                ]);
        }
        return $targetProducts;
    }
    // public function index()
    // {
    //     $products = DB::table('collected_products')
    //         ->where('id', '>=', 1100)
    //         ->where('id', '<=', 4039)
    //         ->get();
    //     foreach ($products as $product) {
    //         $output = [];
    //         $parsedUrl = parse_url($product->productHref);
    //         $domain = $parsedUrl['host'];
    //         // 'www.' 제거
    //         $domain = str_replace('www.', '', $domain);
    //         if ($domain == 'dometopia.com') {
    //             $domain = 'dometopia.js';
    //         }
    //         if ($domain == 'babonara.co.kr') {
    //             $domain = 'babonara.js';
    //         }
    //         if ($domain == 'metaldiy.com') {
    //             $domain = 'metaldiy.js';
    //         }
    //         if ($domain == 'domeggook.com') {
    //             $domain = 'domemedb.js';
    //         }
    //         $script = public_path('js/price/' . $domain);
    //         $command = "node " . escapeshellarg($script) . " " . escapeshellarg($product->productHref);
    //         set_time_limit(0);
    //         exec($command, $output, $returnCode);
    //         if ($returnCode == 0 && isset($output[0])) {
    //             // 성공적으로 처리된 경우, 상품 정보를 추가하고 반복문을 종료
    //             $productPrice = json_decode($output[0], true);
    //             DB::table('collected_products')->where('id', $product->id)->update([
    //                 'productPrice' => $productPrice['productPrice'],
    //                 'updatedAt' => now()
    //             ]);
    //         }
    //         echo "Success";
    //     }
    // }
}
