<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function index()
    {
        if (Auth::guard('partner')->check()) {
            return redirect()->route('partner.dashboard');
        }
        return view('partner/auth/login');
    }
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required',
                'password' => 'required'
            ], [
                'required' => '유효한 계정이 아닙니다.'
            ]);
            $credentials = [
                'email' => $request->email,
                'password' => $request->password,
            ];
            if (Auth::guard('partner')->attempt($credentials)) {
                $partner = Auth::guard('partner')->user();
                $emailVerifiedAt = $partner->email_verified_at;
                if ($emailVerifiedAt === null) {
                    $this->logout();
                    return Redirect::back()->withErrors(['invalidLogin' => '이메일 인증을 완료해주세요.'])->withInput();
                }
                $isActive = $partner->is_active;
                if ($isActive === 'PENDING') {
                    $this->logout();
                    return Redirect::back()->withErrors(['invalidLogin' => '관리자의 승인을 대기 중입니다.'])->withInput();
                }
                if ($isActive === 'INACTIVE') {
                    $this->logout();
                    return Redirect::back()->withErrors(['invalidLogin' => '승인이 거부된 회원입니다. 올바르지 못한 회원 정보 혹은 사업자 정보입니다.'])->withInput();
                }
                return redirect()->to('/partner');
            }
            throw ValidationException::withMessages([
                'invalidLogin' => '유효한 계정이 아닙니다.',
            ]);
        } catch (ValidationException $e) {
            return Redirect::back()->withErrors(['invalidLogin' => '유효한 계정이 아닙니다.'])->withInput();
        }
    }
    public function logout()
    {
        Auth::guard('partner')->logout();
        return redirect()->to('/partner');
    }
}
