<?php

namespace App\Http\Controllers\Namewing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EditProductNameController extends Controller
{
    private $userController;
    public function __construct()
    {
        $this->userController = new UserController();
    }
    public function index(Request $request)
    {
        $rememberToken = $request->rememberToken;
        $validateRememberTokenResult = $this->userController->validateRememberToken($rememberToken);
        if ($validateRememberTokenResult === false) {
            return [
                'status' => false,
                'return' => "로그인 세션이 만료되었습니다. 다시 로그인해주십시오."
            ];
        }
        $productCode = $request->productCode;
        $newProductName = $request->newProductName;
        return $this->updateNewProductName($productCode, $newProductName);
    }
    private function updateNewProductName($productCode, $newProductName)
    {
        try {
            DB::table('minewing_products')
                ->where('productCode', $productCode)
                ->update([
                    'productName' => $newProductName,
                    'updatedAt' => now()
                ]);
            return [
                'status' => true,
                'return' => '"상품명을 성공적으로 수정했습니다."'
            ];
        } catch (\Exception $e) {
            return [
                'status' => true,
                'return' => '"상품명 수정에 실패했습니다. 기술자에게 문의해주십시오."',
                'error' => $e->getMessage()
            ];
        }
    }
}
