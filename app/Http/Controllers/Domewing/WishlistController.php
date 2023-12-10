<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function showWishlist() {

        $wishlist = $this->loadWishlist();
        return view('domewing.wishlist', ['wishlist'=>$wishlist]);
    }

    public function loadWishlist(){
        $member = Auth::guard('member')->user();

        $wishlist = DB::table('wishlist')
                    ->join('uploaded_products', 'wishlist.product_id','=','uploaded_products.id')
                    ->join('collected_products', 'uploaded_products.productId', '=', 'collected_products.id')
                    ->where('user_id', $member->id)
                    ->where('is_Active', 'Y')
                    ->select(
                        'uploaded_products.newImageHref as image',
                        'collected_products.productName',
                        'uploaded_products.id'
                    )
                    ->get();

        return $wishlist;
    }

    public function searchWishlist(Request $request) {
        $member = Auth::guard('member')->user();
        $searchKeyword = $request->input('search_keyword');

        $wishlist = DB::table('wishlist')
            ->join('uploaded_products', 'wishlist.product_id','=','uploaded_products.id')
            ->join('collected_products', 'uploaded_products.productId', '=', 'collected_products.id')
            ->where('user_id', $member->id)
            ->where('is_Active', 'Y')
            ->where('collected_products.productName', 'like', '%'.$searchKeyword.'%')
            ->select(
                'uploaded_products.newImageHref as image',
                'uploaded_products.newProductName as productName',
                'uploaded_products.id'
            )
            ->get();

        return view('domewing.wishlist', ['wishlist' => $wishlist, 'search_keyword' => $searchKeyword,]);
    }
}
