<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    // 로그인 페이지 표시
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 로그인 시도
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
                'is_active' => 'ACTIVE' // 'ACTIVE' 상태인 사용자만 로그인 허용
            ];

            if (Auth::guard('user')->attempt($credentials)) {
                return redirect()->to('/admin/dashboard');
            }

            throw ValidationException::withMessages([
                'invalidLogin' => '유효한 계정이 아닙니다.',
            ]);
        } catch (ValidationException $e) {
            return Redirect::back()->withErrors(['invalidLogin' => '유효한 계정이 아닙니다.'])->withInput();
        }
    }

    // 로그아웃
    public function logout()
    {
        Auth::guard('user')->logout(); // 현재 사용자 로그아웃
        return redirect()->to('/auth/login'); // 로그아웃 후 리디렉션할 경로
    }
}
