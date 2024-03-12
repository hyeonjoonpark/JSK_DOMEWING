<?php

namespace App\Http\Controllers\SellwingApis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'return' => '유효한 계정이 아닙니다.'
            ];
        }
        $email = $request->email;
        $password = $request->password;
        $isValidAccountResult = $this->isValidAccount($email, $password);
        if ($isValidAccountResult['status'] === false) {
            return [
                'status' => false,
                'return' => '유효한 계정이 아닙니다.'
            ];
        }
        return true;
    }
    private function isValidAccount($email, $password)
    {
        $user = DB::table('users')
            ->where('email', $email)
            ->first();
        $hashedPassword = $user->password;
        if (Hash::check($password, $user->password)) {
            return [
                'status' => true
            ];
        }
        return [
            'status' => false,
            'return' => '유효한 계정이 아닙니다.'
        ];
    }
}
