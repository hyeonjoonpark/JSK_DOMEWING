<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerification;

class RegisterController extends Controller
{
    public function index()
    {
        return view('partner.auth.register');
    }
    public function main(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:FREE,PLUS,PREMIUM',
            'name' => 'required|string|min:2|max:10',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'phone' => 'required|digits:11',
            'businessNumber' => 'required|digits:10',
            'businessName' => 'required|min:2|max:20',
            'businessImage' => 'required|file|mimes:jpg,jpeg,gif,png',
            'ppt' => ['required', 'accepted']
        ], [
            'type' => '유효한 파트너 유형을 선택해주세요.',
            'name' => '성명은 최소 2글자, 최대 10글자 이내로 입력해주세요.',
            'email' => '유효한 이메일 주소를 입력해주세요.',
            'password' => '비밀번호는 8자 이상으로 설정해주세요.',
            'password.confirmed' => '입력하신 비밀번호와 확인 비밀번호가 일치하지 않습니다.',
            'phone' => '휴대폰 번호는 11자리로 입력해주세요.',
            'businessNumber' => '사업자 번호는 10자리로 입력해주세요.',
            'businessName' => '사업자 명의는 최소 2글자, 최대 20글자로 입력해주세요.',
            'businessImage' => '사업자 등록증 사본은 jpg, jpeg, gif, png 확장자만 허용됩니다.',
            'ppt.required' => '이용약관을 읽은 후 동의하세요.',
            'ppt.accepted' => '이용약관을 읽은 후 동의하세요.'
        ]);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }
        $businessImage = $this->businessImageHandle($request->businessImage);
        if ($businessImage['status'] === false) {
            return Redirect::back()->withErrors(['businessImage' => $businessImage['return']])->withInput();
        }
        $businessImage = $businessImage['return'];
        $registerResult = $this->register($request->type, $request->name, $request->email, $request->password, $request->phone, $request->businessNumber, $request->businessName, $businessImage);
        if ($registerResult['status'] === false) {
            return Redirect::back()->withErrors(['error' => $registerResult['return']])->withInput();
        }
        $partner = $registerResult['return'];
        Mail::to($partner->email)->send(new EmailVerification([
            'name' => $partner->name,
            'token' => $partner->token
        ]));
        return redirect()->route('partner.login', ['name' => $partner->name]);
    }
    private function register($type, $name, $email, $password, $phone, $businessNumber, $businessName, $businessImage)
    {
        try {
            $partner = Partner::create([
                'type' => $type,
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'phone' => $phone,
                'business_number' => $businessNumber,
                'business_name' => $businessName,
                'business_image' => $businessImage
            ]);
            return [
                'status' => true,
                'return' => $partner
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => '이미 등록된 회원 정보입니다.'
            ];
        }
    }
    private function businessImageHandle($businessImage)
    {
        $destinationPath = 'public/images/business-license';
        $destinationPath = str_replace('/', DIRECTORY_SEPARATOR, $destinationPath);
        try {
            if (!Storage::exists($destinationPath)) {
                Storage::makeDirectory($destinationPath, 0775, true, true);
            }
            $extension = $businessImage->getClientOriginalExtension();
            $fileName = Str::uuid() . '.' . $extension;
            $businessImage->storeAs($destinationPath, $fileName);
            return [
                'status' => true,
                'return' => $fileName
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage()
            ];
        }
    }
}
