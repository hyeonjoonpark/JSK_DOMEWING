<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function showCheckoutPage(Request $request, $id){

        $getOrder = $this->getOrder($id);
        $getUserDetails = $this->getUserDetails();

        return view('domewing.checkout', ['getOrder' => $getOrder, 'getUserDetails'=> $getUserDetails]);
    }

    public function getOrder($id){

        $getOrder = DB::table('order_items')
                    ->join('uploaded_products', 'order_items.product_id', '=', 'uploaded_products.id')
                    ->join('collected_products','uploaded_products.productId','=', 'collected_products.id')
                    ->join('users', 'collected_products.userId', '=', 'users.id')
                    ->where('order_items.order_id', $id)
                    ->where('order_items.status', 'Y')
                    ->select('order_items.*',
                                'users.company as supplier_name',
                                'collected_products.productName',
                                'uploaded_products.newImageHref as image',
                                'collected_products.productPrice as price',
                                'collected_products.shippingCost as shippingCost')
                    ->get();

        return $getOrder;
    }

    public function getUserDetails(){
        $member = Auth::guard('member')->user();

        $getUserDetails = DB::table('members')->where('remember_token', $member->remember_token)->where('is_active','ACTIVE')->first();

        return $getUserDetails;
    }
}
