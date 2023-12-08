<?php

namespace App\Http\Controllers\Domewing\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function showResetPasswordPage(Request $request){

        return view('domewing.auth.reset_password');
    }

    public function resetPassword(Request $request){

        $reset_key = $request->input('resetKey');

        $member = DB::table('members')->where('reset_key', $reset_key)->where('reset_status', 'ACTIVE')->first();

        if(!$member){
            return redirect('/domewing')->with('error', 'This Link is Invalid or Expired.');
        }

        $validator = Validator::make($request->all(),[
            'passwordInput' => 'required|min:8',
            'confirmPassword' => 'required|same:passwordInput',
        ], [
            'required' => 'This field is required.',
            'passwordInput.min' => 'Password must have at least 8 characters.',
            'confirmPassword.same' => 'Password does not match.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $newPassword = $request->input('passwordInput');

        try{
            $update = DB::table('members')->where('id', $member->id)
                        ->update([
                            'password' => bcrypt($newPassword),
                            'reset_status'=> 'INACTIVE',
                            'reset_at' => now(),
                        ]);

            if($update){
                return redirect()->back()->with('success', 'New Password Had Been Set!');
            }else{
                return redirect()->back()->with('error', 'Failed to Set New Password.');
            }
        }catch (Exception $e){
            return redirect()->back()->with('error', 'Error Occured. Please Try Again Later');
        }
    }
}
