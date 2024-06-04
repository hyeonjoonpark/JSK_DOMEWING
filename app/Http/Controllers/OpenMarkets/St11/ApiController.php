<?php

namespace App\Http\Controllers\OpenMarkets\St11;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function builder()
    {
        $data = [
            'key' => '88c03bfe65fa93f959564aa1caf2769a',
            'apiCode' => 'ProductSearch',
            'keyword' => 'test'
        ];
        $response = Http::get('https://openapi.11st.co.kr/openapi/OpenApiService.tmall', $data);
        if ($response->successful()) {
            $xml = simplexml_load_string($response);
        }
        echo "false";
        return;
    }
}
