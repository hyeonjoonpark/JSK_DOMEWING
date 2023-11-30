<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ShoppingCartController extends Controller
{
    public function showShoppingCart(Request $request){

        $shopping_cart = $this->getShoppingCart();

        return view('domewing.shopping_cart', ['shopping_cart'=>$shopping_cart]);
    }

    public function getShoppingCart(){
        $user = Auth::guard('member')->user();

        $margin = DB::table('domewing_margin_rate')
                    ->where('id', 1)
                    ->first()
                    ->rate;

        $shopping_cart = DB::table('members')
                        ->join('shopping_cart', 'members.id', '=', 'shopping_cart.user_id')
                        ->join('uploaded_products', 'shopping_cart.product_id', '=', 'uploaded_products.id')
                        ->join('collected_products', 'uploaded_products.productId', '=', 'collected_products.id')
                        ->join('users', 'collected_products.userId', '=', 'users.id')
                        ->join('cms_domain', 'users.id', '=', 'cms_domain.user_id')
                        ->where('members.remember_token', $user->remember_token)
                        ->where('shopping_cart.is_Active', 'ACTIVE')
                        ->where('collected_products.isActive', 'Y')
                        ->where('uploaded_products.isActive', 'Y')
                        ->where('users.is_active', 'ACTIVE')
                        ->where('cms_domain.is_active', 'ACTIVE')
                        ->select('uploaded_products.id as upload_id',
                                'collected_products.userId as seller_id',
                                'users.company as supplier_name',
                                'uploaded_products.newImageHref as image',
                                'collected_products.productPrice as price',
                                'collected_products.productName',
                                'collected_products.shippingCost as shippingCost',
                                'cms_domain.domain_name as domain_name',
                                'shopping_cart.*')
                        ->get();

        foreach ($shopping_cart as $item) {
            // Calculate the new price by multiplying productPrice with margin
            $newPrice = $item->price * ($margin / 100 + 1);

            // Update the price in the shopping cart item
            $item->price = $newPrice;
        }

        return $shopping_cart;
    }

    public function removeCartItem(Request $request){
        $remember_token = $request->input('remember_token');
        $cart_id = $request->input('cart_id');

        $member = DB::table('members')->where('remember_token', $remember_token)->first();

        if(!$member){
            Auth::guard('member')->logout();
            return [
                'status' => -2,
                'icon' => 'warning',
                'title' => 'Opps',
                'return' => 'User Must Login to Access This Feature'
            ];
        }

        $cart = DB::table('shopping_cart')->where('id', $cart_id)->where('user_id', $member->id)->where('is_Active', 'ACTIVE')->first();

        if(!$cart){
            return [
                'status' => -1,
                'icon' => 'warning',
                'title' => 'Opps',
                'return' => 'Item Not Found. Please Refresh Page.'
            ];
        }

        $update= DB::table('shopping_cart')
                    ->where('id', $cart_id)
                    ->where('is_Active', 'ACTIVE')
                    ->update(['is_Active' => 'INACTIVE','updated_at' => now()]);

        if($update){
            return [
                'status' => 1,
                'icon' => 'success',
                'return' => 'Item Removed',
            ];
        }else{
            return [
                'status' => -1,
                'icon' => 'warning',
                'title' => 'Opps',
                'return' => 'Error Occured. Please Try Again Later.'
            ];
        }
    }

    public function updateQuantity(Request $request){
        $cartId = $request->input('cartId');
        $newQuantity = $request->input('newQuantity');

        $update= DB::table('shopping_cart')
                    ->where('id', $cartId)
                    ->where('is_Active', 'ACTIVE')
                    ->update(['quantity' => $newQuantity,'updated_at' => now()]);
    }
}
