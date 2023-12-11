<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NameController extends Controller
{
    public function index($productName)
    {
        $productName = $this->replaceForbiddenWords($productName);
        $productName = $this->filterString($productName);
        return $productName;
    }
    public function replaceForbiddenWords($productName)
    {
        // Define the array of forbidden words
        $forbiddenWords = config('forbidden_words');
        // Replace each forbidden word in the product name with a space
        foreach ($forbiddenWords as $word) {
            $productName = str_replace($word, ' ', $productName);
        }

        // Return the sanitized product name
        return $productName;
    }
    function filterString($str)
    {
        // 한글, 숫자, 영어, 공백만 허용
        $str = preg_replace('/[^a-zA-Z0-9\s가-힣]/', '', $str);

        // 연속된 공백을 하나로 줄임
        $str = preg_replace('/\s+/', ' ', $str);

        // 앞뒤 공백 제거
        $str = trim($str);

        // 문자열을 바이트 단위로 안전하게 자르기
        $byteLimit = 50;
        $byteCount = 0;
        $resultStr = '';

        for ($i = 0; $i < mb_strlen($str, 'UTF-8') && $byteCount <= $byteLimit; $i++) {
            $char = mb_substr($str, $i, 1, 'UTF-8');
            $byteCount += strlen($char);

            if ($byteCount <= $byteLimit) {
                $resultStr .= $char;
            }
        }

        return $resultStr;
    }
}
