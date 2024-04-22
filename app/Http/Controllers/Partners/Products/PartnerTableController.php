<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PartnerTableController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productTableName' => 'required|string|max:255'
        ], [
            'productTableName' => '상품 테이블명은 최소 1글자, 최대 255글자여야 합니다.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
        $productTableName = trim($request->productTableName);
        $partnerId = DB::table('partners')
            ->where('api_token', $request->apiToken)
            ->first('id')
            ->id;
        return $this->add($partnerId, $productTableName);
    }
    private function add($partnerId, $productTableName)
    {
        $exists = DB::table('partner_tables')
            ->where('partner_id', $partnerId)
            ->where('title', $productTableName)
            ->where('is_active', 'Y')
            ->exists();
        if ($exists === true) {
            return [
                'status' => false,
                'message' => '중복된 상품 테이블명입니다.'
            ];
        }
        try {
            DB::table('partner_tables')
                ->insert([
                    'partner_id' => $partnerId,
                    'title' => $productTableName,
                    'token' => Str::uuid()
                ]);
            return [
                'status' => true,
                'message' => '상품 테이블을 성공적으로 생성했습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '유효한 접근이 아닙니다.',
                'error' => $e->getMessage()
            ];
        }
    }
}
