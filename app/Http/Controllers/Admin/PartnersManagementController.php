<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use App\Models\PartnerClass;

class PartnersManagementController extends Controller
{
    public function index()
    {
        Partner::where('expired_at', '<', now())->update([
            'partner_class_id' => 1
        ]);
        $partners = Partner::with('partnerClass')->orderBy('created_at', 'desc')->get();
        $partnerClasses = PartnerClass::where('is_active', 'ACTIVE')->get();
        return view('admin/partners', [
            'partners' => $partners,
            'partnerClasses' => $partnerClasses
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
        $partnerClassId = $request->partnerClassId;
        $expiredAt = $request->expiredAt;
        try {
            Partner::where("token", $token)->update([
                'partner_class_id' => $partnerClassId,
                'expired_at' => $expiredAt . ' 23:59:59'
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
