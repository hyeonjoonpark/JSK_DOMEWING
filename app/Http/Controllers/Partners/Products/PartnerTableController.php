<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator as ValidationValidator;

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
            $tableId = DB::table('partner_tables')
                ->insertGetId([
                    'partner_id' => $partnerId,
                    'title' => $productTableName,
                    'token' => Str::uuid()
                ]);
            return [
                'status' => true,
                'message' => '상품 테이블을 성공적으로 생성했습니다.',
                'data' => [
                    'tableId' => $tableId
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '유효한 접근이 아닙니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    public function updatePartnerTable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|exists:partner_tables,token',
            'title' => 'required|min:2'
        ], [
            'token' => "유효한 테이블을 선택해주세요.",
            'title' => "테이블명은 최소 2자 이상이어야 합니다."
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'error' => $validator->errors()
            ];
        }
        $token = $request->token;
        $title = $request->title;
        return $this->editPartnerTable($token, $title);
    }
    public function editPartnerTable($partnerTableToken, $title)
    {
        try {
            DB::table('partner_tables')
                ->where('token', $partnerTableToken)
                ->update([
                    'title' => $title
                ]);
            return [
                'status' => true,
                'message' => '테이블명을 성공적으로 수정했습니다.',
                'data' => []
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '테이블을 수정하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    public function deletePartnerTable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'partnerTableToken' => 'required|string|exists:partner_tables,token'
        ], [
            'partnerTableToken' => "유효한 테이블을 선택해주세요."
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'error' => $validator->errors()
            ];
        }
        $partnerTableToken = $request->partnerTableToken;
        return $this->destroyPartnerTable($partnerTableToken);
    }

    protected function destroyPartnerTable($partnerTableToken)
    {
        try {
            DB::table('partner_tables')
                ->where('token', $partnerTableToken)
                ->update([
                    'is_active' => 'N'
                ]);
            DB::table('partner_products AS pp')
                ->join('partner_tables AS pt', 'pt.id', '=', 'pp.partner_table_id')
                ->where('pt.token', $partnerTableToken)
                ->delete();
            return [
                'status' => true,
                'message' => "해당 테이블을 성공적으로 삭제했습니다.",
                'data' => []
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => "테이블을 삭제하는 과정에서 오류가 발생했습니다.",
                'error' => $e->getMessage()
            ];
        }
    }
}
