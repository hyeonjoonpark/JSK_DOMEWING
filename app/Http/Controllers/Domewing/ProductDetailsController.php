<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProductDetailsController extends Controller
{
    public function loadProductDetail(Request $request, $id){

        //Load all the product details here
        $productInfo = DB::table('uploaded_products')
                        ->join('collected_products', 'uploaded_products.productId', '=', 'collected_products.id')
                        ->where('uploaded_products.id', $id)
                        ->where('uploaded_products.isActive', 'Y')
                        ->select('collected_products.*', 'uploaded_products.id as uploadedId')
                        ->first();

        if($productInfo == null){
            return redirect('/domewing')->with('error', 'Product not found');
        }

        $otherProducts = $this->getOtherProducts($productInfo->userId, $productInfo->uploadedId);
        $similarProducts = $this->getSimilarProducts($productInfo->userId, $productInfo->uploadedId);

        return view('domewing.product_detail', [
            'productInfo' => $productInfo,
            'otherProducts' => $otherProducts,
            'similarProducts' => $similarProducts,
        ]);
    }

    public function getOtherProducts($seller_id, $product_id){

        //Getting top 10 item from the same seller
        $otherProducts = DB::table('uploaded_products')
                        ->join('collected_products', 'uploaded_products.productId', '=', 'collected_products.id')
                        ->where('collected_products.userId', $seller_id)
                        ->where('uploaded_products.id', '!=', $product_id)
                        ->where('uploaded_products.isActive', 'Y')
                        ->select('collected_products.*', 'uploaded_products.id as upload_id')
                        ->limit(10)
                        ->get();

        return $otherProducts;
    }

    public function getSimilarProducts($seller_id, $product_id){

        //for testing purpose, need to change this algorithm to fins similar products
        $similarProducts = DB::table('uploaded_products')
                        ->join('collected_products', 'uploaded_products.productId', '=', 'collected_products.id')
                        ->where('collected_products.userId', $seller_id)
                        ->where('uploaded_products.id', '!=', $product_id)
                        ->where('uploaded_products.isActive', 'Y')
                        ->select('collected_products.*', 'uploaded_products.id as upload_id')
                        ->limit(10)
                        ->get();

        return $similarProducts;
    }

    public function addToCart(Request $request){
        $productId = $request->input('productId');
        $quantity = $request->input('quantity');
        $remember_token = $request->input('remember_token');

        $member = DB::table('members')->where('remember_token', $remember_token)->first();
        $product = DB::table('uploaded_products')
            ->join('collected_products', 'uploaded_products.productId', '=', 'collected_products.id')
            ->where('uploaded_products.id', $productId)
            ->where('uploaded_products.isActive', 'Y')
            ->where('collected_products.isActive', 'Y')
            ->select('collected_products.userId as seller_id')
            ->first();

        // Validations
        if (!$member) {
            Auth::logout();
            return [
                'status' => -2,
                'icon' => 'warning',
                'title' => 'Opps',
                'return' => 'User Must Login to Access This Feature'
            ];
        } elseif (!$product) {
            return [
                'status' => -1,
                'icon' => 'error',
                'title' => 'Opps',
                'return' => 'Product Not Found'
            ];
        } elseif ($quantity < 1) {
            return [
                'status' => -1,
                'icon' => 'error',
                'title' => 'Opps',
                'return' => 'Quantity Invalid'
            ];
        }

        // Check duplicate carts
        $checkCart = DB::table('shopping_cart')
            ->where('product_id', $productId)
            ->where('user_id', $member->id)
            ->where('is_Active', 'ACTIVE')
            ->first();

        if ($checkCart) {
            return [
                'status' => -1,
                'icon' => 'warning',
                'title' => 'Info',
                'return' => 'Item Already Added into Your Cart'
            ];
        }

        // Remove all shopping cart items if user adds new item from a different supplier
        $removeCart = DB::table('shopping_cart')
            ->join('uploaded_products', 'shopping_cart.product_id', '=', 'uploaded_products.id')
            ->join('collected_products', 'uploaded_products.productId', '=', 'collected_products.id')
            ->where('shopping_cart.user_id', $member->id)
            ->where('shopping_cart.is_Active', 'ACTIVE')
            ->where('uploaded_products.isActive', 'Y')
            ->where('collected_products.isActive', 'Y')
            ->where('collected_products.userId', '!=', $product->seller_id)
            ->count();

        if ($removeCart > 0) {
            return [
                'status' => -3,
                'icon' => 'warning',
                'title' => 'Info',
                'return' => 'Shopping Cart only can place item from single supplier. Do you want to remove all items from your current shopping cart first?'
            ];
        }

        // Save shopping cart items
        try {
            $cart = [
                'product_id' => $productId,
                'user_id' => $member->id,
                'quantity' => $quantity,
                'created_at' => now(),
            ];

            $addToCart = DB::table('shopping_cart')->insert($cart);

            if ($addToCart) {
                return [
                    'status' => 1,
                    'icon' => 'success',
                    'return' => 'Item Added to Shopping Cart'
                ];
            } else {
                return [
                    'status' => -1,
                    'icon' => 'error',
                    'title' => 'Opps',
                    'return' => 'Failed'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => -1,
                'icon' => 'error',
                'title' => 'Opps',
                'return' => 'Failed to Add Item'
            ];
        }
    }

    public function removeAllCartItem(Request $request){
        $remember_token = $request->input('remember_token');

        $member = DB::table('members')->where('remember_token', $remember_token)->first();

        if(!$member){
            Auth::logout();
            return [
                'status' => -2,
                'icon' => 'warning',
                'title' => 'Opps',
                'return' => 'Session Invalid. Please Login Again.'
            ];
        }

        $update= DB::table('shopping_cart')
                    ->where('user_id', $member->id)
                    ->where('is_Active', 'ACTIVE')
                    ->update(['is_Active' => 'INACTIVE','updated_at' => now()]);

        if($update){
            return [
                'status' => 1,
                'icon' => 'success',
                'return' => 'Items Removed Successfully From Shopping Cart.'
            ];
        }
    }
}
