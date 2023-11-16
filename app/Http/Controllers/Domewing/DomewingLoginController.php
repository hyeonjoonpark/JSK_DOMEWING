<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class DomewingLoginController extends Controller
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
            // Authentication successful
            return redirect()->to('/domewing');
        } else {
            // Authentication failed
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->to('/domewing/auth/login');
    }
}
