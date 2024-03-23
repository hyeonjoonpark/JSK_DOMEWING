<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPassword;

class ForgotPasswordController extends Controller
{
    public function index()
    {
        return view('partner/auth/forgot_password');
    }
    public function main(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ], [
            'email' => '유효한 이메일 주소를 기입해주세요.'
        ]);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }
        $email = $request->input('email');
        $partner = Partner::where('email', $email)->first();
        if ($partner === null) {
            return Redirect::back()->withErrors(['email' => '유효한 이메일 주소를 기입해주세요.'])->withInput();
        }
        $tempPassword = Str::random(16);
        Partner::where('id', $partner->id)->update([
            'password' => bcrypt($tempPassword)
        ]);
        try {
            Mail::to($partner->email)->send(new ForgotPassword([
                'name' => $partner->name,
                'tempPassword' => $tempPassword
            ]));
            return Redirect::back()->withErrors(['email' => '임시 비밀번호 발급 이메일을 성공적으로 보냈습니다.'])->withInput();
        } catch (\Exception $e) {
            return Redirect::back()->withErrors(['email' => '이메일 전송 과정에서 오류가 발생했습니다. 다시 시도해주십시오.'])->withInput();
        }
    }
}
