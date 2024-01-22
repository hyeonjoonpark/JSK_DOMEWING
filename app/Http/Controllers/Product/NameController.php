<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NameController extends Controller
{
    public function index($productName, $byte = 50)
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
        $replaceWords = config('replace_words');
        // Replace each forbidden word in the product name with a space
        foreach ($replaceWords as $word) {
            $productName = str_replace($word, 'JS', $productName);
        }
        foreach ($forbiddenWords as $word) {
            $productName = str_replace($word, ' ', $productName);
        }
        $productName = str_replace(['×', '*'], 'X', $productName);
        // Return the sanitized product name
        return $productName;
    }
    public function filterString($str)
    {
        // 한글, 숫자, 영어, 공백, 마침표만 허용 (유니코드 사용)
        $str = preg_replace('/[^\p{L}\p{N}\s\.]/u', '', $str);
        // 이제 연속된 마침표를 단일 마침표로 대체합니다.
        $str = preg_replace('/\.{2,}/', '.', $str);
        // 연속된 공백을 하나로 줄임
        $str = preg_replace('/\s+/', ' ', $str);
        // 앞뒤 공백 제거
        $str = trim($str);
        return $str;
    }
    public function limitProductName($productName, $maxByte = 50)
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
