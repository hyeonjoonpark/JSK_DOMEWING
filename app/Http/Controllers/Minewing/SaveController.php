<?php

namespace App\Http\Controllers\Minewing;

use App\Http\Controllers\Admin\ProductDataValidityController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\NameController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaveController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $rememberToken = $request->rememberToken;
        $products = $request->products;
        $categoryID = $request->categoryID;
        $productKeywords = $request->productKeywords;
        $isValid = $this->validateElements($categoryID, $productKeywords);
        if (!$isValid['status']) {
            return $isValid;
        }
        $userID = DB::table('users')
            ->where('remember_token', $rememberToken)
            ->where('is_active', 'ACTIVE')
            ->select('id')
            ->first()
            ->id;
        if ($userID === null) {
            return [
                'status' => false,
                'return' => '잘못된 접근입니다.'
            ];
        }
        $nameController = new NameController();
        $productImageController = new ProductImageController();
        $controller = new Controller();
        foreach ($products as $product) {
            $hasOption = $product['hasOption'];
            $sellerID = $product['sellerID'];
            $productName = $product['productName'];
            $seller = $controller->getSeller($sellerID);
            $hasWatermark = $seller->has_watermark;
            $imageScraper = $seller->image_scraper;
            if ($imageScraper === 'Y') {
                $runImageScraperResult = $this->runImageScraper([$product['productImage']]);
                if ($runImageScraperResult['status'] === false) {
                    return $runImageScraperResult;
                }
                $imageFileName = $runImageScraperResult['return'][0]['newFileName'];
                $filePath = public_path("images/CDN/tmp/");
                $productImageSrc = $filePath . $imageFileName;
            } else {
                $productImageSrc = $product['productImage'];
            }
            $productImage = $productImageController->index($productImageSrc, $hasWatermark)['return'];
            $headerImage = DB::table('product_search')
                ->where('vendor_id', $product['sellerID'])
                ->select('header_image')
                ->first()
                ->header_image;
            if (isset($product['productDetail'])) {
                $productDetail = $productImageController->processImages($product['productDetail'], $headerImage);
            } else {
                $productDetail = '<center><img src="https://www.sellwing.kr/images/CDN/' . $headerImage . '"></center>';
            }
            $productPrice = (int)$product['productPrice'];
            $productHref = $product['productHref'];
            if ($hasOption === 'true' && isset($product['productOptions'])) {
                $productOptions = $product['productOptions'];
                $optionPriceType = $this->getOptionPriceType($sellerID);
                $type = 1;
                $numberOfOptions = count($productOptions);
                $numberOfDigits = strlen((string)$numberOfOptions);
                $productNameBytes = 50 - (6 + $numberOfDigits);
                $productName = $nameController->index($productName, $productNameBytes);
                foreach ($productOptions as $productOption) {
                    $newProductName = $productName . ' 옵션 ' . $type;
                    $newProductDetail = '<h1 style="color:red !important; font-weight:bold !important; font-size:4rem !important;">옵션명 : ' . $productOption['optionName'] . '</h1><br><br>' . $productDetail;
                    if ($optionPriceType == 'ADD') {
                        $productPrice = (int)$product['productPrice'] + (int)$productOption['optionPrice'];
                    } else {
                        $productPrice = (int)$productOption['optionPrice'];
                    }
                    $response = $this->insertProducts($sellerID, $userID, $categoryID, $newProductName, $productKeywords, $productPrice, $productImage, $newProductDetail, $hasOption, $productHref);
                    if (!$response['status']) {
                        return $response;
                    }
                    $type++;
                }
            } else {
                $productName = $nameController->index($productName);
                $response = $this->insertProducts($sellerID, $userID, $categoryID, $productName, $productKeywords, $productPrice, $productImage, $productDetail, $hasOption, $productHref);
                if (!$response['status']) {
                    return $response;
                }
            }
        }
        return [
            'status' => true,
            'return' => '"상품셋을 성공적으로 저장했어요!"'
        ];
    }
    private function runImageScraper($imageSrcArr)
    {
        $imageMap = [];
        foreach ($imageSrcArr as $imageSrc) {
            $extension = pathinfo(parse_url($imageSrc, PHP_URL_PATH), PATHINFO_EXTENSION);
            $newFileName = uniqid() . "." . $extension;
            $imageMap[] = [
                "newFileName" => $newFileName,
                "imageSrc" => $imageSrc
            ];
        }
        $tempFilePath = storage_path('app/public/image-src/' . uniqid() . '.json');
        file_put_contents($tempFilePath, json_encode($imageMap));
        $script = public_path('js/image-tracker/main.js');
        $command = "node {$script} {$tempFilePath}";
        exec($command, $output, $resultCode);
        if ($resultCode === 0) {
            return [
                'status' => true,
                'return' => $imageMap
            ];
        }
        return [
            'status' => false,
            'return' => "이미지 스크래핑에 실패했습니다."
        ];
        unlink($tempFilePath);
    }
    public function validateElements($categoryID, $productKeywords)
    {
        $productDataValidityController = new ProductDataValidityController();
        $isValid = $productDataValidityController->index($categoryID, $productKeywords);
        return $isValid;
    }
    public function validateCategoryID($categoryID)
    {
        $isExist = DB::table('ownerclan_category')
            ->where('id', $categoryID)
            ->exists();
        return $isExist;
    }
    public function getOptionPriceType($sellerID)
    {
        $optionPriceType = DB::table('product_search')
            ->where('vendor_id', $sellerID)
            ->select('optionPriceType')
            ->first()
            ->optionPriceType;
        return $optionPriceType;
    }
    public function getIsVAT($sellerID)
    {
        $isVAT = DB::table('product_search')
            ->where('vendor_id', $sellerID)
            ->select('is_vat')
            ->first()
            ->is_vat;
        return $isVAT;
    }
    public function getHasWatermark($sellerID)
    {
        $hasWatermark = DB::table('product_search')
            ->where('vendor_id', $sellerID)
            ->select('has_watermark')
            ->first()
            ->has_watermark;
        return $hasWatermark;
    }
    protected function insertMappingwing($ownerclanCategoryID)
    {
        try {
            $isExist = DB::table('category_mapping')
                ->where('ownerclan', $ownerclanCategoryID)
                ->exists();
            if (!$isExist) {
                DB::table('category_mapping')
                    ->insert([
                        'ownerclan' => $ownerclanCategoryID
                    ]);
            }
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function insertProducts($sellerID, $userID, $categoryID, $productName, $productKeywords, $productPrice, $productImage, $productDetail, $hasOption, $productHref)
    {
        try {
            $isVAT = $this->getIsVAT($sellerID);
            if ($isVAT == 'Y') {
                $productPrice = ceil((int)$productPrice * 1.1);
            }
            do {
                $productCode = $this->generateRandomProductCode(5);
                $isExist = DB::table('minewing_products')
                    ->where('productCode', $productCode)
                    ->where('isActive', 'Y')
                    ->exists();
            } while ($isExist);
            if ($hasOption === 'true') {
                $hasOption = 'Y';
            } else {
                $hasOption = 'N';
            }
            DB::table('minewing_products')
                ->insert([
                    'sellerID' => $sellerID,
                    'userID' => $userID,
                    'categoryID' => $categoryID,
                    'productCode' => $productCode,
                    'productName' => $productName,
                    'productKeywords' => $productKeywords,
                    'productPrice' => $productPrice,
                    'productImage' => $productImage,
                    'productDetail' => $productDetail,
                    'productHref' => $productHref,
                    'hasOption' => $hasOption
                ]);
            $response = $this->insertMappingwing($categoryID);
            if (!$response) {
                return [
                    'status' => false,
                    'return' => '"매핑윙 연동에 실패했습니다."'
                ];
            }
            return [
                'status' => true,
                'return' => '"상품셋을 성공적으로 저장했어요!"'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage()
            ];
        }
    }
    protected function generateRandomProductCode($length)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);
        $randomCode = '';

        for ($i = 0; $i < $length; $i++) {
            $randomCode .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomCode;
    }
}
