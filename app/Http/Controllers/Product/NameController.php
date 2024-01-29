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
    public function limitProductName($string, $limit = 50)
    {
        $byteCount = 0;
        $limitedString = '';

        for ($i = 0; $i < mb_strlen($string, 'UTF-8'); $i++) {
            $char = mb_substr($string, $i, 1, 'UTF-8');
            $byteCount += (strlen($char) >= 2) ? 2 : 1; // 한글 2바이트, 그 외 1바이트 계산

            if ($byteCount <= $limit) {
                $limitedString .= $char;
            } else {
                break;
            }
        }

        return $byteCount;
    }
}
