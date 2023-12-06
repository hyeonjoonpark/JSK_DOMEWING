<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ToReceiveController extends Controller
{
    public function showToReceive() {

        $groupedOrders = $this->getOrders();

        return view('domewing.to_receive', ['groupedOrders' => $groupedOrders]);
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
                    ->where('delivery_details.delivery_status', '2')
                    ->where('transaction_order.status', 'PAID')
                    ->where('transaction_order.user_id', $member->id)
                    ->get();

        $groupedOrders = $orders->groupBy('order_id');

        return $groupedOrders;
    }

    public function confirmReceived(Request $request){
        $remember_token = $request->input('remember_token');
        $transaction_id = $request->input('transaction_id');

        $member = DB::table('members')->where('remember_token', $remember_token)->first();

        if(!$member){
            Auth::guard('member')->logout();
            return [
                'status' => -2,
                'title' => 'OPPS',
                'return' => 'Session Expired. Please Login Again.',
            ];
        }

        $transaction = DB::table('transaction_order')->where('transaction_id', $transaction_id)->where('user_id', $member->id)->first();

        if(!$transaction){
            return [
                'status' => -1,
                'title' => 'ERROR',
                'return' => 'Order Not Found. Please Refresh Page',
            ];
        }

        $update= DB::table('delivery_details')
                    ->where('transaction_id', $transaction_id)
                    ->update(['delivery_status' => '3','updated_at' => now()]);

        if($update){
            return [
                'status' => 1,
                'title' => 'SUCCESS',
                'return' => 'Order Collected Successfully.',
            ];
        }else{
            return [
                'status' => -1,
                'title' => 'ERROR',
                'return' => 'Unexpected Error Occured. Please Try Again Later.',
            ];
        }
    }
}
