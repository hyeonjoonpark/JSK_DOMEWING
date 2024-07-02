<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmailVerificationController extends Controller
{
    public function main(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string', 'exists:partners,token']
        ]);
        DB::table('partners')
            ->where('token', $request->input('token'))
            ->update([
                'email_verified_at' => now()
            ]);
        $title = $validator->fails() ? "인증 실패" : "인증 성공";
        $content = $validator->fails() ? "유효한 이메일 인증 코드가 아닙니다." : "이메일 인증을 성공적으로 완료했습니다.";
        return view('/partner/auth/verify_email_result', [
            'title' => $title,
            'content' => $content
        ]);
    }
}
