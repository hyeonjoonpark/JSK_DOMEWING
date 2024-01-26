<?php

namespace App\Http\Controllers\APIwing;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class SellerController extends Controller
{
    public function threemro()
    {
        set_time_limit(0); // 스크립트 실행 시간 제한을 해제합니다.
        ini_set('memory_limit', '-1'); // 메모리 제한을 해제합니다.

        try {
            // API URL
            $apiUrl = "http://3mro.co.kr/shop/api/api_out.php?div=all&m_no=11841";

            // Make GET request with increased timeout
            $response = Http::timeout(1000000)->get($apiUrl);

            // Check if the request was successful
            if ($response->successful()) {
                // Parse XML response
                $xmlString = $response->body();
                $xmlData = simplexml_load_string($xmlString, null, LIBXML_NOCDATA);

                // Convert XML to JSON for easier manipulation
                $jsonData = json_decode(json_encode($xmlData), true);
                return [
                    'status' => true,
                    'return' => $jsonData['product'],
                ];
            } else {
                return [
                    'status' => false,
                    'return' => 'API 요청에 실패했습니다. 응답 코드: ' . $response->status(),
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => false,
                'return' => 'API 요청 중 예외가 발생했습니다. Error: ' . $e->getMessage(),
            ];
        }
    }
}
