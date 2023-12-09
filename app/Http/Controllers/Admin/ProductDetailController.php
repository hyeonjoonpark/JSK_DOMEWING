<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductDetailController extends Controller
{
    public function index(Request $request)
    {
        $platformStr = $request->platform;
        $vendor = DB::table('vendors')->where('name', $platformStr)->select('name_eng')->first();
        $href = $request->href;
        $script = public_path('js/details/' . $vendor->name_eng . '.js');
        $command = "node " . escapeshellarg($script) . " " . escapeshellarg($href);
        try {
            set_time_limit(0);
            exec($command, $output, $returnCode);
            $data = json_decode($output[0], true);
            return $this->getResponseData(1, $data);
        } catch (\Exception $e) {
            return $this->getResponseData(-1, $e->getMessage());
        }
    }
    public function bulk(Request $request)
    {
        $products = $request->products;
        $processedProducts = [];
        $maxRetries = 3; // 최대 재시도 횟수 설정

        foreach ($products as $product) {
            $retryCount = 0;

            while ($retryCount < $maxRetries) {
                $output = []; // 초기화
                $vendor = DB::table('vendors')->where('name', $product['platform'])->select('name_eng')->first();
                if (!$vendor) {
                    // 벤더 정보가 없는 경우, 이번 상품 처리를 중단
                    break;
                }

                $href = $product['href'];
                $script = public_path('js/details/' . $vendor->name_eng . '.js');
                $command = "node " . escapeshellarg($script) . " " . escapeshellarg($href);
                set_time_limit(0);
                exec($command, $output, $returnCode);

                if ($returnCode == 0 && isset($output[0])) {
                    // 성공적으로 처리된 경우, 상품 정보를 추가하고 반복문을 종료
                    $productDetail = json_decode($output[0], true);
                    $processedProduct = [
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'href' => $product['href'],
                        'detail' => $productDetail['productDetail'],
                        'image' => $product['image'],
                        'platform' => $product['platform']
                    ];
                    $processedProducts[] = $processedProduct;
                    break;
                }

                // 실패한 경우, 재시도 횟수를 증가시킴
                $retryCount++;
            }
        }

        return $this->getResponseData(1, $processedProducts);
    }


    protected function getResponseData($status, $return)
    {
        return ['status' => $status, 'return' => $return];
    }
}
