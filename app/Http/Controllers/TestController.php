<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class TestController extends Controller
{
    public function index()
    {
        // 기본 GET 요청
        $response = Http::get('http://3mro.co.kr/shop/api/api_out.php?div=all&m_no=11841');

        // 응답 확인
        $statusCode = $response->status(); // 상태 코드
        $data = $response->json(); // JSON 응답을 배열로 파싱

        // 예외 처리
        if ($response->failed()) {
            // 실패 시 로직
        }

        return [
            'status' => $statusCode,
            'return' => $data
        ];
    }
}
