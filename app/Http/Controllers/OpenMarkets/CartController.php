<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function saveCart($order, $domewingAndPartner)
    {
        try {
            $product = $this->getProduct($order['productCode']);
            $cartData = $this->prepareCartData($domewingAndPartner, $product, $order['quantity']);
            $this->insertCartData($cartData);
            return [
                'status' => true,
                'message' => '카트 저장에 성공하였습니다.',
                'data' => $cartData
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '카트 저장에 실패하였습니다.',
                'error' => $e->getMessage(),
            ];
        }
    }
    private function getProduct($productCode)
    {
        return DB::table('minewing_products')
            ->where('productCode', $productCode)
            ->where('is_active', 'Y')
            ->first();
    }
    private function prepareCartData($domewingAndPartner, $product, $quantity)
    {
        return [
            'member_id' => $domewingAndPartner->domewing_account_id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'code' => Str::uuid(),
            'status' => 'PAID'
        ];
    }
    private function insertCartData($cartData)
    {
        return DB::table('carts')->insert($cartData);
    }
}
