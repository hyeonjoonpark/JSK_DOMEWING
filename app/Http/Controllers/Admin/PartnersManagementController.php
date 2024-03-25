<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PartnersManagementController extends Controller
{
    public function index()
    {
        $partners = Partner::orderBy('created_at', 'desc')->get();
        return view('admin/partners', [
            'partners' => $partners
        ]);
    }
    public function updateIsActive(Request $request)
    {
        $token = $request->token;
        $isActive = $request->isActive;
        try {
            Partner::where('token', $token)->update(['is_active' => $isActive]);
            return [
                'status' => true,
                'return' => '파트너스 회원의 상태를 성공적으로 변경했습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage()
            ];
        }
    }
    public function updateType(Request $request)
    {
        $token = $request->token;
        $type = $request->type;
        $expiredAt = $request->expiredAt;
        try {
            Partner::where("token", $token)->update([
                'type' => $type,
                'expired_at' => $expiredAt
            ]);
            return [
                'status' => true,
                'return' => '해당 파트너의 타입을 성공적으로 업데이트했습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage()
            ];
        }
    }
}
