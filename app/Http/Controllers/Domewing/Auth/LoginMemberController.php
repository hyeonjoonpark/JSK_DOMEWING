<?php

namespace App\Http\Controllers\Domewing\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class LoginMemberController extends Controller
{
    public function showLoginForm(Request $request){
        return view('domewing.auth.login');
    }

    public function login(Request $request){

        $validator= $request->validate([
            'email' => 'required',
            'password' => 'required'
        ], [
            'required' => 'This field is required'
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('member')->attempt($credentials)) {
            $user = Auth::guard('member')->user();

            if ($user && $user->is_active === 'ACTIVE') {
                // Authentication successful and user is active
                return redirect()->to('domewing');
            } elseif ($user && $user->is_active === 'PENDING') {
                // Handle PENDING state, redirect to a verification page or show a message
                Auth::guard('member')->logout();
                return back()->withErrors(['email' => 'Email Not Verified'])->withInput();
            } else {
                // Handle other cases like INACTIVE or unexpected states
                Auth::guard('member')->logout();
                return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
            }
        } else {
            // Authentication failed
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }
    }

    public function logout()
    {
        Auth::guard('member')->logout();
        return redirect()->to('/domewing/auth/login');
    }
}
