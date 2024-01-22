<?php

namespace App\Http\Controllers\APIwing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Minewing\SaveController;
use App\Http\Controllers\Product\InsertController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

class MainController extends Controller
{
    public function index($vendorID)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $seller = DB::table('product_search AS ps')
            ->join('vendors AS v', 'v.id', '=', 'ps.vendor_id')
            ->where('ps.vendor_id', $vendorID)
            ->first();
        if ($seller == null) {
            return [
                'status' => false,
                'return' => '유효한 원청사가 아닙니다.'
            ];
        }
        $sellerEngName = $seller->name_eng;
        $sellerController = new SellerController();
        $response = $sellerController->$sellerEngName();
        if ($response['status'] == false) {
            return $response;
        }
        $products = $response['return'];
        return $products;
        $optionColNameController = new OptionColNameController();
        foreach ($products as $index => $product) {
        }
    }
    public function threeMRO($product)
    {
        $sellerID = 16;
        $userID = 15;
        $categoryID = null;
        $saveController = new SaveController();
        do {
            $productCode = $saveController->generateRandomProductCode(5);
            $isExist = DB::table('minewing_products')
                ->where('productCode', $productCode)
                ->where('isActive', 'Y')
                ->exists();
        } while ($isExist);
        $productName = $product['prdtname'];
        $productKeywords = '';
        for ($i = 1; $i <= 5; $i++) {
            $keyword = $product['keyword']['keyword' . $i];
            if ($keyword != '') {
                if ($productKeywords != '') {
                    $keyword .= ',' . $keyword;
                }
                $productKeywords .= $keyword;
            }
        }
        $productPrice = (int)$product['price']['buyprice'];
        $productImage = $product['listimg']['url'];
        $productDetail = '<center><img src="https://www.sellwing.kr/images/CDN/' . $headerImage . '"></center>' . $product['content'];
    }
}
