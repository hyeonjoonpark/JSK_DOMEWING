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
            DB::table('margin_rate')
                ->where('id', $mrID)
                ->update([
                    'rate' => $rate
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
}
