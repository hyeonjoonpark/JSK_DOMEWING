<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShippingFeeController extends Controller
{
    public function index(Request $request)
    {
        $rememberToken = $request->rememberToken;
        $response = $this->getUser($rememberToken);
        if (!$response['status']) {
            return $response;
        }
        $user = $response['return'];
        $vendorID = $request->vendorID;
        $shippingFee = $request->shippingFee;
        $response = $this->editShippingFee($vendorID, $shippingFee);
        return $response;
    }
    public function editShippingFee($vendorID, $shippingFee)
    {
        try {
            DB::table('product_search')
                ->where('vendor_id', $vendorID)
                ->update([
                    'shipping_fee' => $shippingFee
                ]);
            return [
                'status' => true,
                'return' => '배송비를 성공적으로 저장했습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => '예기치 못한 오류가 발생했습니다. 기술자에게 문의해 주십시오. ' . $e->getMessage()
            ];
        }
    }
    public function getUser($rememberToken)
    {
        $user = DB::table('users')
            ->where('is_active', 'ACTIVE')
            ->where('remember_token', $rememberToken)
            ->first();
        if ($user == null) {
            return [
                'status' => false,
                'return' => '세션 정보가 만료되었습니다. 다시 로그인해 주십시오.'
            ];
        }
        return [
            'status' => true,
            'return' => $user
        ];
    }
}
