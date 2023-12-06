<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ToShipController extends Controller
{
    public function showToShip() {

        $groupedOrders = $this->getOrders();

        return view('domewing.to_ship', ['groupedOrders' => $groupedOrders]);
    }

    public function getOrders(){
        $member = Auth::guard('member')->user();

        $orders = DB::table('transaction_order')
                    ->join('order', 'transaction_order.order_id','=','order.order_id')
                    ->join('delivery_details', 'transaction_order.transaction_id','=','delivery_details.transaction_id')
                    ->join('order_items', 'order.order_id', '=', 'order_items.order_id')
                    ->join('uploaded_products', 'order_items.product_id', '=', 'uploaded_products.id')
                    ->join('collected_products', 'uploaded_products.productId','=','collected_products.id')
                    ->join('users', 'collected_products.userId', '=','users.id')
                    ->select(
                        'users.company as supplierName',
                        'collected_products.productName',
                        'uploaded_products.newImageHref',
                        'transaction_order.transaction_id',
                        'order.order_id',
                        DB::raw('(order_items.price_at * order_items.quantity + order_items.shipping_at) as total_price'))
                    ->where('delivery_details.delivery_status', '1')
                    ->where('transaction_order.status', 'PAID')
                    ->where('transaction_order.user_id', $member->id)
                    ->get();

        $groupedOrders = $orders->groupBy('order_id');

        return $groupedOrders;
    }
}
