<?php

namespace App\Http\Controllers\OpenMarkets\St11;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class UploadController extends Controller
{
    /**
     * @param Collection $products
     * @param Collection $partner
     * @param Collection $account
     */
    public function main($products, $partner, $account): array
    {
        $ac = new ApiController();
        $apiKey = $account->access_key;
        $method = 'post';
        $url = 'http://api.11st.co.kr/rest/prodservices/product';
        $data = $this->getData($products);
        $builderResult = $ac->builder($apiKey, $method, $url, $data);
        return [
            'status' => false,
            'data' => $builderResult
        ];
    }
    private function getData($products)
    {
        return [
            'Product' => []
        ];
    }
}
