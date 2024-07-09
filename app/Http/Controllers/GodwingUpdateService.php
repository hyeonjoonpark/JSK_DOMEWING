<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GodwingUpdateService extends Controller
{
    public function main(Request $request)
    {
        $validatorResult = $this->validator($request);
        if (!$validatorResult['status']) {
            return $validatorResult;
        }
        return response()->json($this->update($request->input('vendorId')));
    }
    protected function validator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendorId' => ['required', 'integer', 'exists:vendors,id']
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => '유효한 업체가 아닙니다.'
            ];
        }
        return [
            'status' => true
        ];
    }
    protected function update($vendorId)
    {
        try {
            DB::table('vendors')
                ->where('id', $vendorId)
                ->update([
                    'is_godwing' => 1
                ]);
            return [
                'status' => true,
                'message' => '해당 업체를 갓윙으로 업데이트했습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '업체를 갓윙으로 업데이트하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
}
