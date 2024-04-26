<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Partner;
use Illuminate\Support\Facades\DB;

class PartnerAccountSetting extends Controller
{
    public function index()
    {
        return view('partner/account_setting_partner');
    }
    public function validatePartner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:10',
            'password' => 'required|min:8|confirmed',
            'phone' => 'required|digits:11',
        ], [
            'name' => '성명은 최소 2글자, 최대 10글자 이내로 입력해주세요.',
            'password' => '비밀번호는 8자 이상으로 설정해주세요.',
            'password.confirmed' => '입력하신 비밀번호와 확인 비밀번호가 일치하지 않습니다.',
            'phone' => '휴대폰 번호는 11자리로 입력해주세요.',
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
        $name = $request->name;
        $phone = $request->phone;
        $password = $request->password;
        $apiToken = $request->apiToken;
        return $this->updatePartner($name, $phone, $password, $apiToken);
    }
    private function updatePartner($name, $phone, $password, $apiToken)
    {
        try {
            Partner::where('api_token', $apiToken)->update([
                'name' => $name,
                'password' => bcrypt($password),
                'phone' => $phone
            ]);
            return [
                'status' => true,
                'message' => '계정 정보를 성공적으로 수정했습니다. 다시 로그인해주십시오.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendorId' => 'required|integer',
        ], [
            'vendorId' => '오픈마켓을 선택해주세요.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
        $vendor = DB::table('vendors AS v')
            ->join('vendor_commissions AS vc', 'vc.vendor_id', '=', 'v.id')
            ->where('v.id', $request->vendorId)
            ->where('v.is_active', 'ACTIVE')
            ->first(['v.name_eng', 'vc.commission']);
        if ($vendor === null) {
            return [
                'status' => false,
                'message' => '유효한 오픈마켓이 아닙니다. 페이지를 새로고침 후, 다시 시도해주세요.'
            ];
        }
        $vendorEngName = $vendor->name_eng;
        $vendorCommission = $vendor->commission;
        $partnerId = DB::table('partners')
            ->where('api_token', $request->apiToken)
            ->first(['id'])
            ->id;
        $accounts = DB::table($vendorEngName . '_accounts')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'ACTIVE')
            ->get(['hash', 'username']);
        if ($accounts->isEmpty()) {
            return [
                'status' => false,
                'message' => '해당 오픈마켓과 연동된 계정이 없습니다. 환경설정에서 계정 연동을 선행해주세요.',
                'redirect' => true
            ];
        }
        return [
            'status' => true,
            'data' => [
                'accounts' => $accounts,
                'vendorCommission' => $vendorCommission
            ]
        ];
    }
}
