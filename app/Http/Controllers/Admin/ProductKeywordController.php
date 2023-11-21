<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductKeywordController extends Controller
{
    function index(Request $request)
    {
        $productName = $request->productName;
        $apiKey = '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'; // OpenAI API 키
        $apiUrl = 'https://api.openai.com/v1/engines/davinci/completions'; // GPT-3 API URL

        $prompt = "suggest 5 keywords for $productName as a bullet point list of the most important points.";

        $data = [
            'prompt' => $prompt,
            'max_tokens' => 100 // 적절한 토큰 수 설정
        ];

        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['choices'][0]['text'])) {
            $data['status'] = 1;
            $data['return'] = trim($result['choices'][0]['text']);
        } else {
            $data['status'] = -1;
            $data['return'] = "키워드 추출에 실패했습니다.";
        }
        return $data;
    }
}