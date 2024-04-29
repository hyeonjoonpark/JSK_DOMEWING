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
    public function delete(Request $request)
    {
        // 컨트롤러
        $validator = Validator::make($request->all(), [
            'tableToken' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }

        $partnerId = DB::table('partners')
            ->where('api_token', $request->apiToken)
            ->first('id');

        if (!$partnerId) {
            return [
                'status' => false,
                'message' => '인증 실패'
            ];
        }

        $partnerId = $partnerId->id;
        $tableToken = $request->tableToken;

        return $this->destroy($partnerId, $tableToken);
    }
    protected function destroy($partnerId, $tableToken)
    {
        $table = DB::table('partner_tables')
            ->where('partner_id', $partnerId)
            ->where('token', $tableToken)
            ->first();

        if (!$table) {
            return response()->json(['status' => false, 'message' => '테이블이 존재하지 않거나 접근 권한이 없습니다.'], 404);
        }

        try {
            DB::table('partner_tables')
                ->where('id', $table->id)
                ->delete();

            return response()->json(['status' => true, 'message' => '테이블이 성공적으로 삭제되었습니다.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => '테이블 삭제 중 오류가 발생했습니다.', 'error' => $e->getMessage()], 500);
        }
    }
}
