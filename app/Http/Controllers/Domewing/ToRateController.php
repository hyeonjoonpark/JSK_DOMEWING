<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ToRateController extends Controller
{
    public function showToRate() {

        $groupedOrders = $this->getOrders();
        $groupedReviewedOrders = $this->getReviewedOrders();

        return view('domewing.to_rate', ['groupedOrders' => $groupedOrders, 'groupedReviewedOrders' => $groupedReviewedOrders]);
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
                    ->leftJoin('review', 'transaction_order.id','=','review.transaction_id')
                    ->select(
                        'users.company as supplierName',
                        'uploaded_products.newProductName as productName',
                        'uploaded_products.newImageHref',
                        'transaction_order.transaction_id',
                        'order.order_id',
                        DB::raw('(order_items.price_at * order_items.quantity + order_items.shipping_at) as total_price'))
                    ->where('delivery_details.delivery_status', '3')
                    ->where('transaction_order.status', 'PAID')
                    ->where('transaction_order.user_id', $member->id)
                    ->whereNull('review.transaction_id')
                    ->get();

        $groupedOrders = $orders->groupBy('order_id');

        return $groupedOrders;
    }

    public function getReviewedOrders(){
        $member = Auth::guard('member')->user();

        $orders = DB::table('transaction_order')
                    ->join('order', 'transaction_order.order_id','=','order.order_id')
                    ->join('delivery_details', 'transaction_order.transaction_id','=','delivery_details.transaction_id')
                    ->join('order_items', 'order.order_id', '=', 'order_items.order_id')
                    ->join('uploaded_products', 'order_items.product_id', '=', 'uploaded_products.id')
                    ->join('collected_products', 'uploaded_products.productId','=','collected_products.id')
                    ->join('users', 'collected_products.userId', '=','users.id')
                    ->join('review', 'transaction_order.id','=','review.transaction_id')
                    ->select(
                        'users.company as supplierName',
                        'uploaded_products.newProductName as productName',
                        'uploaded_products.newImageHref',
                        'transaction_order.transaction_id',
                        'order.order_id',
                        'review.rating',
                        'review.review',
                        DB::raw('(order_items.price_at * order_items.quantity + order_items.shipping_at) as total_price'))
                    ->where('delivery_details.delivery_status', '3')
                    ->where('transaction_order.status', 'PAID')
                    ->where('transaction_order.user_id', $member->id)
                    ->get();

        $groupedOrders = $orders->groupBy('order_id');

        return $groupedOrders;
    }

    public function submitReview(Request $request){
        $remember_token = $request->input('remember_token');
        $transaction_id = $request->input('transaction_id');
        $rating = $request->input('rating');
        $review = $request->input('review');

        if($rating < 1 || $rating > 5){
            return response()->json(['rating' => 'Please Provide Your Rating'], 422);
        }

        $member = DB::table('members')->where('remember_token', $remember_token)->first();
        $transaction = DB::table('transaction_order')->where('transaction_id', $transaction_id)->first();
        $duplicateReview = DB::table('review')->where('transaction_id', $transaction->id)->first();

        if(!$member){
            Auth::guard('member')->logout();
            return [
                'status' => -2,
                'title' => 'OPPS',
                'return' => 'Session Expired. Please Login Again.',
            ];
        }else if(!$transaction){
            return [
                'status' => -1,
                'title' => 'ERROR',
                'return' => 'Order Not Found. Please Try Again Later.',
            ];
        }else if($duplicateReview){
            return [
                'status' => -1,
                'title' => 'OPPS',
                'return' => 'You Had Submmitted This Review.',
            ];
        }

        $reviewData = [
            'transaction_id' => $transaction->id,
            'rating' => $rating,
            'review' => $review,
            'created_at' => now(),
        ];

        $addReview = DB::table('review')->insert($reviewData);

        if($addReview){
            return [
                'status' => 1,
                'title' => 'SUCCESS',
                'return' => 'Review Submitted Successfully.',
            ];
        }else{
            return [
                'status' => -1,
                'title' => 'ERROR',
                'return' => 'Unexpected Error Occured. Please Try Again Later.',
            ];
        }
    }

    public function editReview(Request $request){
        $remember_token = $request->input('remember_token');
        $transaction_id = $request->input('transaction_id');
        $rating = $request->input('rating');
        $review = $request->input('review');

        if($rating < 1 || $rating > 5){
            return response()->json(['rating' => 'Please Provide Your Rating'], 422);
        }

        $member = DB::table('members')->where('remember_token', $remember_token)->first();
        $transaction = DB::table('transaction_order')->where('transaction_id', $transaction_id)->first();

        if(!$member){
            Auth::guard('member')->logout();
            return [
                'status' => -2,
                'title' => 'OPPS',
                'return' => 'Session Expired. Please Login Again.',
            ];
        }else if(!$transaction){
            return [
                'status' => -1,
                'title' => 'ERROR',
                'return' => 'Order Not Found. Please Try Again Later.',
            ];
        }

        $reviewData = [
            'rating' => $rating,
            'review' => $review,
            'updated_at' => now(),
        ];

        $addReview = DB::table('review')->where('transaction_id',$transaction->id)->update($reviewData);

        if($addReview){
            return [
                'status' => 1,
                'title' => 'SUCCESS',
                'return' => 'Review Updated Successfully.',
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
