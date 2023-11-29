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
        $url = "https://api-gateway.coupang.com/v2/providers/openapi/apis/api/v1/categorization/predict"; // API URL
        $data = array('productName' => '닥터락 삶는 고급형 방수시트 노랑 병원 요양원 침대반시트'); // 보낼 데이터

        // cURL 세션 초기화
        $ch = curl_init();

        // cURL 옵션 설정
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // 데이터를 URL-인코딩 형식으로 변환
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // 요청 실행 및 응답 받기
        $response = curl_exec($ch);

        // cURL 세션 종료
        curl_close($ch);

        // 응답 출력
        echo $response;
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
