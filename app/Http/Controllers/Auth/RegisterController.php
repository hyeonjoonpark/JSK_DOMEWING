<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function index()
    {
        return view('auth/register');
    }

    protected function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company' => ['required', 'min:2', 'max:20'],
            'name' => ['required', 'min:2', 'max:10', 'regex:/^[가-힣]+$/'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'min:8', 'max:16', 'confirmed'],
            'ppt' => ['required', 'accepted'],
        ], [
            'company.required' => '업체명은 필수 항목입니다.',
            'company.min' => '업체명은 최소 2글자 이상이어야 합니다.',
            'company.max' => '업체명은 최대 20글자까지 허용됩니다.',
            'name.required' => '이름은 필수 항목입니다.',
            'name.min' => '이름은 최소 2글자 이상이어야 합니다.',
            'name.max' => '이름은 최대 10글자까지 허용됩니다.',
            'name.regex' => '이름은 한글로만 입력해야 합니다.',
            'email.required' => '이메일은 필수 항목입니다.',
            'email.email' => '올바른 이메일 형식을 입력하세요.',
            'email.max' => '이메일은 255자까지 허용됩니다.',
            'email.unique' => '이미 등록된 이메일입니다.',
            'password.required' => '비밀번호는 필수 항목입니다.',
            'password.min' => '비밀번호는 최소 8글자 이상이어야 합니다.',
            'password.max' => '비밀번호는 최대 16글자까지 허용됩니다.',
            'password.confirmed' => '확인 비밀번호와 일치하지 않습니다.',
            'ppt.required' => '이용약관을 읽은 후 동의하세요.',
            'ppt.accepted' => '이용약관을 읽은 후 동의하세요.',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $user = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'remember_token' => Str::random(60),
            'created_at' => now(),
            'updated_at' => now(),
            'company' => $request->input('company'),
        ];

        DB::table('users')->insert($user);
        Mail::to($user['email'])->send(new EmailVerification([
            'name' => $user['name'],
            'remember_token' => $user['remember_token']
        ]));

        return redirect()->route('auth.login');
    }
    protected function verifyEmail(Request $request)
    {
        try {
            $token = $request->input('token');
            $user = DB::table('partners')->where([
                'token' => $token,
                'email_verified_at' => null
            ])->first();
            if ($user === null) {
                return view('auth/verify_email_result', [
                    'title' => "이메일 인증에 실패했습니다",
                    'content' => "유효한 링크가 아닙니다."
                ]);
            }
            DB::table('partners')->where('token', $token)->update([
                'email_verified_at' => now()
            ]);
            $title = "이메일을 성공적으로 인증했습니다";
            $content = "가입하신 이메일과 비밀번호로 로그인이 가능합니다.";
        } catch (Exception $e) {
            $title = "이메일 인증에 실패했습니다";
            $content = "유효한 링크가 아닙니다.";
        }
        return view('auth/verify_email_result', [
            'title' => $title,
            'content' => $content
        ]);
    }
}
