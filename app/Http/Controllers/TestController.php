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
use Exception;
use Str;

class TestController extends Controller
{
    // public function index()
    // {
    //     $this->deactiveProduct();
    //     $products = $this->getProducts();
    //     foreach ($products as $product) {
    //         $productName = $this->preprocessProductName($product->productName);
    //         $upID = $product->upID;
    //         $this->updateUP($upID, $productName);
    //     }
    // }
    public function index()
    {
        $this->deactiveProduct();
        $products = $this->getProducts();
        foreach ($products as $product) {
            $newProductPrice = $this->newProductPrice($product->sellerID, $product->productPrice);
            $this->updatePrice($newProductPrice, $product->cpID);
        }
    }
    public function updatePrice($newProductPrice, $productID)
    {
        try {
            DB::table('collected_products')
                ->where('id', $productID)
                ->update([
                    'productPrice' => $newProductPrice
                ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    public function newProductPrice($sellerID, $price)
    {
        $vATSellers = [3, 13, 14]; // 도매토피아, 비츠온, 씨오코리아
        foreach ($vATSellers as $vATSeller) {
            if ($sellerID === $vATSeller) {
                $price += $price * 0.1;
                $price = (int) round($price, 0);
            }
        }
        return $price;
    }
    public function updateUP($upID, $productName)
    {
        try {
            DB::table('uploaded_products')
                ->where('id', $upID)
                ->update([
                    'newProductName' => $productName,
                    'updatedAt' => now()
                ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    public function deactiveProduct()
    {
        try {
            DB::table('collected_products')
                ->where('productName', 'LIKE', '%품절%')
                ->update([
                    'isActive' => 'N'
                ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    public function getProducts()
    {
        $products = DB::table('collected_products AS cp')
            ->join('uploaded_products AS up', 'cp.id', '=', 'up.productId')
            ->where('cp.isActive', 'Y')
            ->where('up.isActive', 'Y')
            ->select('*', 'up.id AS upID', 'cp.id AS cpID')
            ->get();
        return $products;
    }
    public function preprocessProductName($productName)
    {
        $productName = $this->replaceForbiddenWords($productName);
        $productName = $this->regexProductName($productName);
        return $productName;
    }
    public function replaceForbiddenWords($productName)
    {
        // Define the array of forbidden words
        $forbiddenWords = [
            '/',
            'CB',
            'EB',
            '옵션선택',
            '색상선택',
            '규격선택',
            '오피스넥스',
            '최고',
            '기미',
            '양모',
            '숙취',
            '치매',
            '무좀',
            '교정',
            '발모',
            '환자',
            '탄력',
            '벨크로',
            '이벤트',
            '염산',
            '스노우볼',
            '슬라이락',
            '오랄비',
            '기미',
            '보약',
            '존슨',
            '성기'
        ];

        // Replace each forbidden word in the product name with a space
        foreach ($forbiddenWords as $word) {
            $productName = str_replace($word, ' ', $productName);
        }

        // Return the sanitized product name
        return $productName;
    }

    public function regexProductName($productName)
    {
        $productName = preg_replace('/[^가-힣a-zA-Z0-9\s]/u', '', $productName);
        $productName = preg_replace('/\s+/', ' ', $productName);
        $productName = trim($productName);
        $productName = mb_substr($productName, 0, 25, 'UTF-8');
        return $productName;
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