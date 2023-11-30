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
        $activedUploadVendors = DB::table('product_register')
            ->join('vendors', 'vendors.id', '=', 'product_register.vendor_id')
            ->where('product_register.is_active', 'Y')
            ->where('vendors.is_active', 'ACTIVE')
            ->get();
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $fc = new FormController();
        $preprocessedProducts = DB::table('uploaded_products')
            ->join('collected_products', 'collected_products.id', '=', 'uploaded_products.productId')
            ->whereBetween('uploaded_products.productId', [502, 974])
            ->get();
        foreach ($activedUploadVendors as $vendor) {
            $vendorEngName = $vendor->name_eng;
            $response = $fc->$vendorEngName($preprocessedProducts, 15);
            if ($response['status'] == 1) {
                $data['return']['successVendors'][] = $vendor->name;
                $data['return']['successVendorsNameEng'][] = $vendorEngName;
                $data['return']['formedExcelFiles'][] = $response['return'];
            }
        }
        return $data;
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
