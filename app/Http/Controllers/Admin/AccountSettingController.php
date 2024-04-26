<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AccountSettingController extends Controller
{
    protected function changeMarginRate(Request $request)
    {
        $validator = $this->validateRate($request);
        if ($validator->fails()) {
            return $this->getResponseData(-1, $validator->errors()->first());
        }
        $marginRate = $request->marginRate;
        return $this->updateMarginRate($request->mrID, $request->marginRate);
    }
    protected function validateRate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'marginRate' => 'required|integer|min:1|max:100'
        ], [
            'marginRate' => '마진율은 반드시 1 이상 100 이하의 정수(%)로 기입해주세요.'
        ]);
        return $validator;
    }
    protected function updateMarginRate($mrID, $rate)
    {
        try {
            DB::table('product_register AS pr')
                ->where('pr.vendor_id', $mrID)
                ->update([
                    'pr.margin_rate' => $rate
                ]);
            return $this->getResponseData(true, '마진율을 성공적으로 변경했습니다.');
        } catch (Exception $e) {
            return $this->getResponseData(false, $e->getMessage());
        }
    }
    protected function getResponseData($status, $return)
    {
        return ['status' => $status, 'return' => $return];
    }
    public function updateVendorCommission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendorEngName' => 'required|string',
            'commission' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        }
        $vendorEngName = $request->vendorEngName;
        $commission = $request->commission;
        DB::table('vendor_commissions AS vc')
            ->join('vendors AS v', 'v.id', '=', 'vc.vendor_id')
            ->where('v.name_eng', $vendorEngName)
            ->update([
                'vc.commission' => $commission
            ]);
        return [
            'status' => true,
            'message' => '오픈 마켓 수수료를 성공적으로 업데이트했습니다.'
        ];
    }
}
