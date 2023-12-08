<?php

namespace App\Http\Controllers\Domewing\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgetPassword;

class ForgetPasswordController extends Controller
{
    public function showForgetPasswordPage(Request $request){
        return view('domewing.auth.forget_password');
    }

    public function submitRequest(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
        ], [
            'required' => 'This field is required.',
            'email.email' => 'Please provide a valid email address.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $email = $request->input('email');
        $member = DB::table('members')->where('email',$email)->where('is_active', '!=', 'INACTIVE')->first();

        if(!$member){
            return redirect()->back()->withErrors(['email'=>'Email Not Found'])->withInput();
        }

        $reset_key = Str::random(60);

        try{
            $submit = DB::table('members')->where('id', $member->id)
                ->update(['reset_key' => $reset_key, 'reset_status'=> 'ACTIVE']);

            if ($submit) {
                Mail::to($email)->send(new ForgetPassword([
                    'name' => $member->username,
                    'remember_token' => $reset_key
                ]));
                return redirect()->back()->with('success', 'Password Recovery Had Sent to Your Email!');
            } else {
                return redirect()->back()->with('error', 'Failed to Submit Request.');
            }

        } catch (Exception $e){
            return redirect()->back()->with('error', 'Error Occured. Please Try Again Later');
        }
    }
}
