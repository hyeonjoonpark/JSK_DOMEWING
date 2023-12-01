<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ProductUploadController extends Controller
{
    public function index()
    {
        try {
            $fc = new FormController();
            $fc->preprocessProductDataset();
            $userID = 15;
            $products = $this->preprocessedProducts();
            $successProductIDs = [];
            $failedProductIDs = [];
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            foreach ($products as $product) {
                if ($this->preprocessProduct($product->id, $userID)) {
                    $successProductIDs[] = $product->id;
                } else {
                    $failedProductIDs[] = $product->id;
                }
            }
            return [
                'success' => $successProductIDs,
                'failed' => $failedProductIDs
            ];
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    public function preprocessedProducts()
    {
        $preprocessedProducts = DB::table('collected_products as cp')
            ->leftJoin('uploaded_products as up', 'up.productId', '=', 'cp.id')
            ->whereNull('up.productId')
            ->where('cp.isActive', 'Y')
            ->select('cp.*')
            ->get();
        return $preprocessedProducts;
    }
    public function preprocessProduct($productID, $userID)
    {
        $product = $this->getProduct($productID);
        $pIC = new ProductImageController();
        $uPC = new UploadedProductController();
        $newProductName = $this->editProductName($product->productName);
        $newImageHref = $pIC->index($product->productImage);
        if (!$newImageHref['status']) {
            $uPC->deactiveProduct($product->id);
            return false;
        }
        $newImageHref = $newImageHref['return'];
        $newProductDetail = $pIC->preprocessProductDetail($product);
        if (!$newProductDetail['status']) {
            $uPC->deactiveProduct($product->id);
            return false;
        }
        $newProductDetail = $newProductDetail['return'];
        $this->insertUploadedProducts($userID, $productID, $newProductName, $newImageHref, $newProductDetail);
        return true;
    }
    public function insertUploadedProducts($userID, $productID, $newProductName, $newImageHref, $newProductDetail)
    {
        DB::table('uploaded_products')
            ->insert([
                'productId' => $productID,
                'userId' => $userID,
                'newImageHref' => $newImageHref,
                'newProductName' => $newProductName,
                'newProductDetail' => $newProductDetail
            ]);
    }
    public function getProduct($productID)
    {
        return DB::table('collected_products')
            ->where('id', $productID)
            ->first();
    }
    public function editProductName($productName)
    {
        $byteLimit = 50;
        $byteCount = 0;
        $editedName = '';
        $previousCharWasSpace = false;
        for ($i = 0; $i < mb_strlen($productName, 'UTF-8') && $byteCount < $byteLimit; $i++) {
            $char = mb_substr($productName, $i, 1, 'UTF-8');
            // 한글, 숫자, 영어, 공백만 허용
            if (!preg_match('/[가-힣0-9a-zA-Z ]/u', $char)) {
                continue;
            }
            // 연속된 공백 방지
            if ($char == ' ') {
                if ($previousCharWasSpace) {
                    continue;
                }
                $previousCharWasSpace = true;
            } else {
                $previousCharWasSpace = false;
            }
            // 한글인 경우 2바이트로 계산
            if (preg_match('/[\x{AC00}-\x{D7AF}]/u', $char)) {
                $byteCount += 2;
            } else {
                // 그 외 문자는 1바이트로 계산
                $byteCount += 1;
            }
            if ($byteCount <= $byteLimit) {
                $editedName .= $char;
            }
        }
        return $editedName;
    }
}
