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
                        ->select(
                            'collected_products.productPrice',
                            'collected_products.shippingCost',
                            'collected_products.userId',
                            'uploaded_products.id as uploadedId',
                            'uploaded_products.newImageHref as image',
                            'uploaded_products.newProductName as productName',
                            'uploaded_products.newProductDetail as productDetail')
                        ->first();

        if($productInfo == null){
            return redirect('/domewing')->with('error', 'Product not found');
        }

        $margin = $this->getMargin();

        $productInfo->productPrice = $productInfo->productPrice * ($margin / 100 + 1);

        $otherProducts = $this->getOtherProducts($productInfo->userId, $productInfo->uploadedId);
        $similarProducts = $this->getSimilarProducts($productInfo->userId, $productInfo->uploadedId);
        $wishlist = $this->checkWishlist($productInfo->uploadedId);

        return view('domewing.product_detail', [
            'productInfo' => $productInfo,
            'otherProducts' => $otherProducts,
            'similarProducts' => $similarProducts,
            'wishlist' => $wishlist,
        ]);
    }

    public function checkWishlist($productId){
        if(!Auth::guard('member')->check()){
            return null;
        }

        $member = Auth::guard('member')->user();

        $wishlist = DB::table('wishlist')->where('product_id', $productId)->where('user_id', $member->id)->where('is_Active', 'Y')->first();

        if (!$wishlist){
            return "add_to_wishlist";
        }else{
            return "remove_from_wishlist";
        }
    }

    public function getMargin(){
        $margin = DB::table('domewing_margin_rate')
                    ->where('id', 1)
                    ->first()
                    ->rate;

        return $margin;
    }

    public function getOtherProducts($seller_id, $product_id){

        //Getting top 10 item from the same seller
        $otherProducts = DB::table('uploaded_products')
                        ->join('collected_products', 'uploaded_products.productId', '=', 'collected_products.id')
                        ->where('collected_products.userId', $seller_id)
                        ->where('uploaded_products.id', '!=', $product_id)
                        ->where('uploaded_products.isActive', 'Y')
                        ->select(
                            'collected_products.productPrice',
                            'uploaded_products.id as upload_id',
                            'uploaded_products.newImageHref as image',
                            'uploaded_products.newProductName as productName',)
                        ->limit(10)
                        ->get();

        $margin = $this->getMargin();

        foreach ($otherProducts as $item) {
            // Calculate the new price by multiplying productPrice with margin
            $newPrice = $item->productPrice * ($margin / 100 + 1);

            // Update the price in the shopping cart item
            $item->productPrice = $newPrice;
        }

        return $otherProducts;
    }

    public function getSimilarProducts($seller_id, $product_id){

        //for testing purpose, need to change this algorithm to fins similar products
        $similarProducts = DB::table('uploaded_products')
                        ->join('collected_products', 'uploaded_products.productId', '=', 'collected_products.id')
                        ->where('collected_products.userId', $seller_id)
                        ->where('uploaded_products.id', '!=', $product_id)
                        ->where('uploaded_products.isActive', 'Y')
                        ->select(
                            'collected_products.productPrice',
                            'uploaded_products.id as upload_id',
                            'uploaded_products.newImageHref as image',
                            'uploaded_products.newProductName as productName',)
                        ->limit(10)
                        ->get();

        $margin = $this->getMargin();

        foreach ($similarProducts as $item) {
            // Calculate the new price by multiplying productPrice with margin
            $newPrice = $item->productPrice * ($margin / 100 + 1);

            // Update the price in the shopping cart item
            $item->productPrice = $newPrice;
        }

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
                'title' => 'ERROR',
                'return' => 'User Must Login To Access.'
            ];
        } else if (!$product) {
            return [
                'status' => -1,
                'title' => 'ERROR',
                'return' => 'Product Not Found'
            ];
        } else if ($quantity < 1) {
            return [
                'status' => -1,
                'title' => 'ERROR',
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
                'title' => 'INFO',
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
                    'title' => 'SUCCESS',
                    'return' => 'Item Added to Shopping Cart.'
                ];
            } else {
                return [
                    'status' => -1,
                    'title' => 'ERROR',
                    'return' => 'Failed to Add Item to Shopping Cart.'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => -1,
                'title' => 'ERROR',
                'return' => 'Failed to Add Item to Shopping Cart.'
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
                'title' => 'ERROR',
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
                'title' => 'SUCCESS',
                'return' => 'Items Removed Successfully From Shopping Cart.'
            ];
        }
    }

    public function addToWishlist(Request $request){
        $productId = $request->input('productId');
        $remember_token = $request->input('remember_token');

        $member = DB::table('members')->where('remember_token', $remember_token)->first();
        $product = DB::table('uploaded_products')
            ->join('collected_products', 'uploaded_products.productId', '=', 'collected_products.id')
            ->where('uploaded_products.id', $productId)
            ->where('uploaded_products.isActive', 'Y')
            ->where('collected_products.isActive', 'Y')
            ->select('collected_products.userId as seller_id')
            ->first();

        $wishlist = DB::table('wishlist')->where('product_id', $productId)->where('user_id', $member->id)->where('is_Active', 'Y')->first();

        // Validations
        if (!$member) {
            Auth::logout();
            return [
                'status' => -2,
                'title' => 'ERROR',
                'return' => 'User Must Login To Access.'
            ];
        } else if (!$product) {
            return [
                'status' => -1,
                'title' => 'ERROR',
                'return' => 'Product Not Found'
            ];
        }else if($wishlist){
            $update = DB::table('wishlist')
                ->where('id', $wishlist->id)
                ->update([
                    'is_Active' => 'N',
                    'updated_at' => now(),
                ]);

            if ($update) {
                return [
                    'status' => 1,
                    'title' => 'SUCCESS',
                    'return' => 'Item Removed From Wishlist.'
                ];
            } else {
                return [
                    'status' => -1,
                    'title' => 'ERROR',
                    'return' => 'Failed to Add Item to Wishlist.'
                ];
            }
        }

        try {
            $wishlist = [
                'product_id' => $productId,
                'user_id' => $member->id,
                'created_at' => now(),
            ];

            $addToWishlist = DB::table('wishlist')->insert($wishlist);

            if ($addToWishlist) {
                return [
                    'status' => 1,
                    'title' => 'SUCCESS',
                    'return' => 'Item Added to Wishlist.'
                ];
            } else {
                return [
                    'status' => -1,
                    'title' => 'ERROR',
                    'return' => 'Failed to Add Item to Wishlist.'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => -1,
                'title' => 'ERROR',
                'return' => 'Failed to Add Item to Wishlist.'
            ];
        }
    }
}
