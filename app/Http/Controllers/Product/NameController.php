<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NameController extends Controller
{
    public function index($productName, $byte)
    {
        $productName = $this->replaceForbiddenWords($productName);
        $productName = $this->filterString($productName);
        $productName = $this->limitProductName($productName, $byte);
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
        // 한글, 숫자, 영어, 공백만 허용 (유니코드 사용)
        $str = preg_replace('/[^\p{L}\p{N}\s]/u', '', $str);
        // 연속된 공백을 하나로 줄임
        $str = preg_replace('/\s+/', ' ', $str);
        // 앞뒤 공백 제거
        $str = trim($str);
        return $str;
    }
    function limitProductName($productName, $maxByte = 50)
    {
        $byteCount = 0;
        $limitedName = '';

        for ($i = 0; $i < mb_strlen($productName); $i++) {
            $char = mb_substr($productName, $i, 1);
            // ASCII 문자는 1byte, 그외는 2byte로 계산
            $byteCount += (ord($char) <= 127) ? 1 : 2;

            if ($byteCount <= $maxByte) {
                $limitedName .= $char;
            } else {
                break;
            }
        }
        $limitedName = trim($limitedName);
        return $limitedName;
    }
}
