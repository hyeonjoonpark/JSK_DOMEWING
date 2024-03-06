<?php

namespace App\Http\Controllers\Gdf;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\ProcessController;
use App\Http\Controllers\Productwing\SoldOutController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VitsonMroController extends Controller
{
    private $processController;
    private $soldOutController;
    private $gdfController;
    const VENDOR_ID = 13;
    const USER_ID = 15;
    public function __construct()
    {
        $this->processController = new ProcessController();
        $this->soldOutController = new SoldOutController();
        $this->gdfController = new GdfController();
    }
    public function main()
    {
        set_time_limit(0);
        ini_set('memory_allow', '-1');
        $directoryPath = storage_path('app/public/gdf/'); // JSON 파일들이 위치한 디렉토리 경로
        $pattern = $directoryPath . '/*.json'; // JSON 파일들에 대한 검색 패턴
        // 지정된 패턴과 일치하는 모든 파일의 경로를 가져옵니다.
        $jsonFiles = glob($pattern);
        $allProductCodes = []; // 모든 상품 코드를 저장할 배열
        foreach ($jsonFiles as $file) {
            // 파일의 내용을 읽어옵니다.
            $content = file_get_contents($file);
            // JSON 문자열을 PHP 배열로 변환합니다.
            $productCodes = json_decode($content, true);
            // 배열이 유효한 경우, 통합 배열에 병합합니다.
            if (is_array($productCodes)) {
                $allProductCodes = array_merge($allProductCodes, $productCodes);
            }
        }
        $b2bs = DB::table('product_register AS pr')
            ->join('vendors AS v', 'v.id', '=', 'pr.vendor_id')
            ->where('pr.is_active', 'Y')
            ->where('v.is_active', 'ACTIVE')
            ->where('pr.vendor_id', 13)
            ->get();
        foreach ($allProductCodes as $productCode) {
            if ($productCode === 'OGNYJ') {
                break;
                foreach ($b2bs as $b2b) {
                    $b2bId = $b2b->id;
                    $vendorEngName = $b2b->name_eng;
                    $account = $this->processController->getAccount(self::USER_ID, $b2bId);
                    $username = $account->username;
                    $password = $account->password;
                    $this->soldOutController->sendSoldOutRequest($productCode, $vendorEngName, $username, $password);
                }
            }
        }
        return $this->gdfController->inactiveProducts($productCodes);
    }
    private function getVitsonMroProducts()
    {
        return DB::table('minewing_products')
            ->where('sellerID', self::VENDOR_ID)
            ->where('isActive', 'Y')
            ->orderBy('createdAt', 'asc')
            ->get(['productCode', 'productHref']);
    }
    private function genHrefsJsonFile($products, $index)
    {
        if ($products instanceof Collection) {
            $productsArray = $products->values()->toArray();
        } else {
            // 이미 배열인 경우, 직접 사용
            $productsArray = $products;
        }
        $tempFilePath = storage_path('app/public/gdf/sibal' . $index . '.json');
        file_put_contents($tempFilePath, json_encode($productsArray));
        return $tempFilePath;
    }
    private function trackOverAmountProducts($tempFilePath)
    {
        $scriptPath = public_path('js/gdf/vitsonmro.js');
        $command = "node {$scriptPath} {$tempFilePath}";
        exec($command, $output, $resultCode);
        if ($resultCode === 0 && isset($output[0])) {
            $productCodes = json_decode($output[0], true);
            return $productCodes;
        }
        return false;
    }
}
