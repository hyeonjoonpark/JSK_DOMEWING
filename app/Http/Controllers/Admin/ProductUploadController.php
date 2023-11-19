<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductUploadController extends Controller
{
    public function index(Request $request)
    {
        $collectedProducts = DB::select("
            SELECT cp.*
            FROM collected_products cp
            LEFT JOIN uploaded_products up ON up.productId = cp.id
            WHERE up.productId IS NULL
            AND cp.id=70;
        ");
        $productsImages = $this->resizeProductImage($collectedProducts['productImage']);
    }
    public function resizeProductImage($imageUrl)
    {
        $data = "";
        return $data;
    }
}
