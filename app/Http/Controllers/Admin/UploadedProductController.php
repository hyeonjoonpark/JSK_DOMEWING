<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class UploadedProductController extends Controller
{
    public function getNewProductDetail($uploadedProductID)
    {
        $product = $this->getProduct($uploadedProductID);
        $pIC = new ProductImageController();
        $preprocessProductDetail = $pIC->preprocessProductDetail($product);
        if ($preprocessProductDetail['status']) {
            return $preprocessProductDetail['return'];
        } else {
            $deactiveProduct = $this->deactiveProduct($product->id);
        }
    }
    public function getNewProductName($uploadedProductID)
    {
        $productName = $this->getProduct($uploadedProductID)->productName;
        $newProductName = $this->editProductName($productName);
        return $newProductName;
    }
    public function deactiveProduct($productID)
    {
        try {
            DB::table('collected_products')
                ->where('id', $productID)
                ->update([
                    'isActive' => 'N',
                    'updatedAt' => now(),
                    'remark' => '이미지 추출에 실패했습니다.'
                ]);
            return [
                'status' => true,
                'return' => 'success'
            ];
        } catch (Exception $e) {
            return [
                'status' => true,
                'return' => $e->getMessage()
            ];
        }
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
