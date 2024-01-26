<?php

namespace App\Http\Controllers\APIwing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\NameController;
use Illuminate\Support\Facades\DB;

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
        $success = 0;
        $error = 0;
        foreach ($products as $index => $product) {
            $response = $this->$sellerEngName($product);
            $success += $response['success'];
            $error += $response['error'];
        }
        return [
            'success' => $success,
            'error' => $error
        ];
    }
    public function threeMRO($product)
    {
        $sellerID = 16;
        $userID = 15;
        $categoryName = trim($product['mrocatenm']);
        $controller = new Controller();
        $table = 'threemro_products';
        $productKeywords = '';
        for ($i = 1; $i <= 5; $i++) {
            $keyword = $product['keyword']['@attributes']['keyword' . $i];
            if ($keyword != '') {
                if ($productKeywords != '') {
                    $keyword .= ',' . $keyword;
                }
                $productKeywords .= $keyword;
            }
        }
        $productPrice = (int)$product['price']['@attributes']['buyprice'];
        $productImage = $product['listimg']['@attributes']['url'];
        $productDetail = '<center><img src="https://www.sellwing.kr/images/CDN/ladam_header.jpg"></center>' . trim($product['content']);
        $productHref = 'https://www.3mro.co.kr/shop/goods/goods_view.php?goodsno=' . $product['@attributes']['code'] . '&category=';
        $nameController = new NameController();
        if ($product['option1'] == '' || is_array($product['option1'])) {
            $productName = $nameController->index(trim($product['prdtname']));
            $hasOption = 'N';
            $response = $controller->insertProduct($table, $sellerID, $userID, $categoryName, $productName, $productKeywords, $productPrice, $productImage, $productDetail, $productHref, $hasOption);
            $success = 0;
            $error = 0;
            if ($response['status'] == true) {
                $success++;
            } else {
                $error++;
            }
            return [
                'success' => $success,
                'error' => $error
            ];
        } else {
            $productName = $nameController->index(trim($product['prdtname']), 42);
            $hasOption = 'Y';
            $productOptions = trim($product['option1']);
            $productOptions = explode(',', $productOptions);
            $optionPrices = trim($product['option1price']);
            $optionPrices = explode(',', $optionPrices);
            $success = 0;
            $error = 0;
            for ($i = 1; $i < count($productOptions); $i++) {
                $newProductName = $productName . ' 옵션 ' . $i;
                $newProductPrice = $productPrice + $optionPrices[$i - 1];
                $newProductDetail = '<h1 style="color:red !important; font-weight:bold !important; font-size:2rem !important;">옵션명 : ' . $productOptions[$i] . '</h1>' . $productDetail;
                $response = $controller->insertProduct($table, $sellerID, $userID, $categoryName, $newProductName, $productKeywords, $newProductPrice, $productImage, $newProductDetail, $productHref, $hasOption);
                if ($response['status'] == true) {
                    $success++;
                } else {
                    $error++;
                }
            }
            return [
                'success' => $success,
                'error' => $error
            ];
        }
    }
}
