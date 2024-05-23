<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WingController extends Controller
{
    public function getBalance(int $memberId): int
    {
        $depositAmount = DB::table('wing_transactions')
            ->where('member_id', $memberId)
            ->where('type', 'DEPOSIT')
            ->where('status', 'APPROVED')
            ->sum('amount');
        $withdrawalAmount = DB::table('wing_transactions AS wt')
            ->join('withdrawal_details AS wd', 'wd.wing_transaction_id', '=', 'wt.id')
            ->where('wt.member_id', $memberId)
            ->where('wt.status', 'APPROVED')
            ->sum('wt.amount');
        $paidAmount = DB::table('wing_transactions AS wt')
            ->join('orders AS o', 'o.wing_transaction_id', '=', 'wt.id')
            ->where('member_id', $memberId)
            ->where('o.type', 'PAID')
            ->where('wt.status', 'APPROVED')
            ->distinct()
            ->sum('wt.amount');
        $refundAmount = DB::table('orders AS o')
            ->join('wing_transactions AS wt', 'o.wing_transaction_id', '=', 'wt.id')
            ->where('wt.member_id', $memberId)
            ->where('o.type', 'REFUND')
            ->where('wt.status', 'APPROVED')
            ->sum('wt.amount');
        $exchangeAmount = DB::table('orders AS o')
            ->join('wing_transactions AS wt', 'o.wing_transaction_id', '=', 'wt.id')
            ->where('wt.member_id', $memberId)
            ->where('o.type', 'EXCHANGE')
            ->where('wt.status', 'APPROVED')
            ->sum('wt.amount');
        $balance = $depositAmount - $withdrawalAmount - $paidAmount + $refundAmount - $exchangeAmount;
        return $balance;
    }

    // 판매 가격 계산
    protected function getSalePrice($productId)
    {
        $originProductPrice = DB::table('minewing_products')
            ->where('id', $productId)
            ->value('productPrice');

        $promotion = DB::table('promotion_products AS pp')
            ->join('promotion AS p', 'p.id', '=', 'pp.promotion_id')
            ->where('product_id', $productId)
            ->where('p.end_at', '>', now())
            ->where('p.is_active', 'Y')
            ->where('pp.is_active', 'Y')
            ->where('p.band_promotion', 'N')
            ->where('pp.band_product', 'N')
            ->value('pp.product_price');

        $productPrice = $promotion ?? $originProductPrice;

        $margin = DB::table('sellwing_config')->where('id', 1)->value('value');
        $marginRate = ($margin / 100) + 1;

        return ceil($productPrice * $marginRate);
    }
}
