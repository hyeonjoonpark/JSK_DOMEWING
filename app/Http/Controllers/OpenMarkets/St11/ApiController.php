<?php

namespace App\Http\Controllers\OpenMarkets\St11;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function builder($apiKey, $method, $url, $data = '')
    {
        $response = Http::withHeaders([
            'Content-type' => 'text/xml;charset=EUC-KR',
            'openapikey' => $apiKey,
        ])->withBody(iconv('UTF-8', 'EUC-KR', $data), 'text/xml')
            ->$method($url);
        if ($response->successful()) {
            $xml = simplexml_load_string($response->body());
            return [
                'status' => true,
                'data' => $xml
            ];
        }
        return [
            'status' => false,
            'message' => '11번가 API 요청을 보내는 과정에서 에러가 발생했습니다.',
            'error' => mb_convert_encoding($response->body(), 'UTF-8', 'EUC-KR')
        ];
    }
}
