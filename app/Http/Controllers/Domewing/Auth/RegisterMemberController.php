<?php

namespace App\Http\Controllers\Domewing\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationMember;

class RegisterMemberController extends Controller
{
    public function showRegisterForm(Request $request){
        return view('domewing.auth.register');
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(),[
            'title' => 'required|min:2',
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required|email',
            'phoneCodeHidden' => 'required',
            'phoneNumber' => 'required|min:6',
            'passwordInput' => 'required|min:8',
            'confirmPassword' => 'required|same:passwordInput',
            'username' => 'required',
        ], [
            'required' => 'This field is required.',
            'title.min' => 'Title must have at least 2 characters.',
            'email.email' => 'Please provide a valid email address.',
            'phoneCodeHidden.required' => 'Please Select a Phone Code.',
            'phoneNumber.min' => 'Phone number must have at least 6 numbers.',
            'passwordInput.min' => 'Password must have at least 8 characters.',
            'confirmPassword.same' => 'Password does not match.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $title = $request->input('title');
        $fname = $request->input('fname');
        $lname = $request->input('lname');
        $email = $request->input('email');
        $phoneCodeHidden = $request->input('phoneCodeHidden');
        $phoneNumber = $request->input('phoneNumber');
        $confirmPassword = $request->input('confirmPassword');
        $username = $request->input('username');
        $remember_token = Str::random(60);

        $checkExistingEmail = DB::table('members')->where('email', $email)->first();

        if($checkExistingEmail){
            return redirect()->back()->withErrors(['email' => 'The email already exists'])->withInput();
        }

        try{
            $register = DB::table('members')->insert([
                'title' => $title,
                'first_name' => $fname,
                'last_name' => $lname,
                'email' => $email,
                'phone_code' => $phoneCodeHidden,
                'phone_number' => $phoneNumber,
                'password' => bcrypt($confirmPassword),
                'username' => $username,
                'remember_token' => $remember_token,
                'created_at' => now(),
                'updated_at' => now(),
                'is_active' => 'PENDING',
            ]);

            if ($register) {
                Mail::to($email)->send(new EmailVerificationMember([
                    'name' => $username,
                    'remember_token' => $remember_token
                ]));
                return redirect()->back()->with('success', 'Registration Successful!');
            } else {
                return redirect()->back()->with('error', 'Failed to Register.');
            }

        } catch (Exception $e){
            return redirect()->back()->with('error', 'Error Occured. Please Try Again Later');
        }
    }

    protected function verifyEmail(Request $request)
    {
        try {
            $remember_token = $request->input('remember_token');
            $user = DB::table('members')->where([
                'remember_token' => $remember_token,
                'email_verified_at' => null
            ])->get();
            if ($user->isEmpty()) {
                return view('domewing/auth/verify_email_result', [
                    'title' => "Email verification failed",
                    'content' => "This is not a valid link."
                ]);
            }
            DB::table('members')->where('remember_token', $remember_token)->update([
                'email_verified_at' => now(),
                'is_active' => 'ACTIVE',
            ]);
            $title = "Your email has been successfully verified";
            $content = "You can log in with the email and password you signed up with.";
        } catch (Exception $e) {
            $title = "Email verification failed";
            $content = "This is not a valid link.";
        }

        return view('domewing/auth/verify_email_result', [
            'title' => $title,
            'content' => $content
        ]);
    }
}
