<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class UploadedProductController extends Controller
{
    public function index($uploadedProductID)
    {
        try {
            $product = $this->getProduct($uploadedProductID);
            $newProductDetail = $this->getNewProductDetail($uploadedProductID);
            if (!$newProductDetail['status']) {
                $this->deactiveProduct($product->productId);
            }
            $newImageHref = $this->getNewImageHref($uploadedProductID);
            if (!$newImageHref['status']) {
                $this->deactiveProduct($product->productId);
            }
            $newProductName = $this->editProductName($product->productName);
            $this->updateUploadedProduct($uploadedProductID, $newProductName, $newProductDetail, $newImageHref);
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    public function updateUploadedProduct($uploadedProductID, $newProductName, $newProductDetail, $newImageHref)
    {
        DB::table('uploaded_products')
            ->where('id', $uploadedProductID)
            ->update([
                'newImageHref' => $newImageHref,
                'newProductDetail' => $newProductDetail,
                'newProductName' => $newProductName
            ]);
    }
    public function getNewProductDetail($uploadedProductID)
    {
        $product = $this->getProduct($uploadedProductID);
        $pIC = new ProductImageController();
        $preprocessProductDetail = $pIC->preprocessProductDetail($product);
        return $preprocessProductDetail;
    }
    public function getNewImageHref($uploadedProductID)
    {
        $product = $this->getProduct($uploadedProductID);
        $pIC = new ProductImageController();
        $newImageHrefResponse = $pIC->index($product->productImage);
        return $newImageHrefResponse;
    }
    public function getNewProductName($uploadedProductID)
    {
        $productName = $this->getProduct($uploadedProductID)->productName;
        $newProductName = $this->editProductName($productName);
        return $newProductName;
    }
    public function deactiveProduct($productID)
    {
        DB::table('collected_products')
            ->where('id', $productID)
            ->update([
                'isActive' => 'N',
                'updatedAt' => now(),
                'remark' => '이미지 추출에 실패했습니다.'
            ]);
    }
    public function getProduct($uploadedProductID)
    {
        $product = DB::table('uploaded_products as up')
            ->join('collected_products as cp', 'cp.id', '=', 'up.productId')
            ->where('up.id', $uploadedProductID)
            ->first();
        return $product;
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
