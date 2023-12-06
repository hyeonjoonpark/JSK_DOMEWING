<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function createOrder(Request $request){
        $selectedIds = $request->input('selectedIds');
        $remember_token = $request->input('remember_token');

        $member = DB::table('members')->where('remember_token', $remember_token)->first();

        //authentication check
        if(!$member){
            Auth::logout();
            $data = [
                'status' => -2,
                'icon' => 'warning',
                'title' => 'Opps',
                'return' => 'User Must Login to Access This Feature'
            ];

            return $data;
        }else if(empty($selectedIds)){
            $data = [
                'status' => -1,
                'icon' => 'warning',
                'title' => 'Opps',
                'return' => 'Select At Least One Item for Checkout'
            ];

            return $data;
        }

        //get selected items from shopping cart
        $selectedItems = DB::table('shopping_cart')->whereIn('id', $selectedIds)->get();

        if(!$selectedItems){
            $data = [
                'status' => -1,
                'icon' => 'warning',
                'title' => 'Opps',
                'return' => 'Select At Least One Item for Checkout'
            ];

            return $data;
        }

        $prefix = 'DWO'; // Prefix for your order ID
        $timestamp = Carbon::now()->format('YmdHis'); // Current timestamp formatted as 'YmdHis'

        $orderId = $prefix . $timestamp;

        //create orders
        $newOrderId = DB::table('order')->insert([
                        'user_id' => $member->id,
                        'created_at' => now(),
                        'order_id' => $orderId,
                    ]);

        try{
            foreach ($selectedItems as $item) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'created_at' => now(),
                ]);
            }

            $data = [
                'status' => 1,
                'icon' => 'success',
                'return' => 'Order done',
                'checkout_id' => $orderId
            ];
        }catch(Exception $e){
            $data = [
                'status' => -1,
                'icon' => 'error',
                'title' => 'Opps',
                'return' => 'Error Occured. Please Try Again Later'
            ];
        }

        return $data;
    }

    public function createSingleOrder(Request $request){
        $product_id = $request->input('productId');
        $quantity = $request->input('quantity');
        $remember_token = $request->input('remember_token');

        $member = DB::table('members')->where('remember_token', $remember_token)->first();
        $product = DB::table('uploaded_products')
                    ->join('collected_products', 'uploaded_products.productId', '=', 'collected_products.id')
                    ->where('uploaded_products.id', $product_id)
                    ->where('uploaded_products.isActive', 'Y')
                    ->where('collected_products.isActive', 'Y')
                    ->first();

        //authentication check
        if(!$member){
            Auth::logout();
            $data = [
                'status' => -2,
                'title' => 'ERROR',
                'return' => 'User Must Login to Access This Feature.'
            ];

            return $data;
        }else if (!$product) {
            return [
                'status' => -1,
                'title' => 'ERROR',
                'return' => 'Product Not Found'
            ];
        }

        $prefix = 'DWO'; // Prefix for your order ID
        $timestamp = Carbon::now()->format('YmdHis'); // Current timestamp formatted as 'YmdHis'

        $orderId = $prefix . $timestamp;

        try{
            //create orders
            $newOrderId = DB::table('order')->insert([
                'user_id' => $member->id,
                'created_at' => now(),
                'order_id' => $orderId,
            ]);

            DB::table('order_items')->insert([
                'order_id' => $orderId,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'created_at' => now(),
            ]);

            $data = [
                'status' => 1,
                'checkout_id' => $orderId
            ];

            return $data;
        }catch (Exception $e){
            return [
                'status' => -1,
                'title' => 'ERROR',
                'return' => 'Unexpected Error Occurred. Please Again Later.'
            ];
        }


    }
}
