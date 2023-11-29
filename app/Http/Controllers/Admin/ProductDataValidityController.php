<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductDataValidityController extends Controller
{
    public function index(Request $request)
    {
        if (!$this->isCategoryValid($request->categoryID)) {
            return ['status' => false, 'message' => '잘못된 카테고리입니다.'];
        }

        $validationResult = $this->validateKeywords($request->keywords);
        return $validationResult === true
            ? ['status' => true]
            : ['status' => false, 'message' => $validationResult];
    }

    private function isCategoryValid($categoryID)
    {
        return DB::table('category')->where('id', $categoryID)->exists();
    }

    private function validateKeywords($keywords)
    {
        $keywordsArray = explode(',', $keywords);
        $numKeywords = count($keywordsArray);
        if ($numKeywords < 5 || $numKeywords > 10) {
            return '키워드의 개수는 5개 이상 10개 이하 입니다.';
        }

        if (count($keywordsArray) !== count(array_unique($keywordsArray))) {
            return '중복된 키워드가 있습니다.';
        }

        foreach ($keywordsArray as $keyword) {
            if (!preg_match('/^[가-힣a-zA-Z0-9]+$/', $keyword)) {
                return '키워드는 공백 없이 한국어, 영어, 숫자로만 가능합니다.';
            }
            if (mb_strlen($keyword) < 2 || mb_strlen($keyword) > 10) {
                return '각 키워드는 2글자 이상 10글자 이하만 가능합니다.';
            }
        }

        $bannedWords = ['최고', '숙취', '치매', '무좀', '품절', '교정', '발모', '환자', '탄력', '이벤트', '벨크로', '밸크로'];
        foreach ($bannedWords as $bannedWord) {
            if (strpos($keyword, $bannedWord) !== false) {
                return $bannedWord . '(은)는 금지된 키워드입니다.';
            }
        }

        return true;
    }
}
?>