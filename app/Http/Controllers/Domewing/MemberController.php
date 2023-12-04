<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MemberController extends Controller
{
    public function loadAccountSettings(Request $request){

        $userInfo = $this->getUserDetails();

        return view('domewing.user_details', ['userInfo' => $userInfo]);
    }

    public function getUserDetails(){
        $member = Auth::guard('member')->user();

        $getUserDetails = DB::table('members')->where('remember_token', $member->remember_token)->where('is_active','ACTIVE')->first();

        return $getUserDetails;
    }

    public function updateProfile(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:2',
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required|email',
            'phoneCodeHidden' => 'required',
            'phoneNumber' => 'required|min:6',
            'username' => 'required',
        ], [
            'required' => 'This field is required.',
            'title.min' => 'Title must have at least 2 characters.',
            'email.email' => 'Please provide a valid email address.',
            'phoneCodeHidden.required' => 'Please select a Phone Code.',
            'phoneNumber.min' => 'Phone number must have at least 6 numbers.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $remember_token = $request->input('remember_token');
        $member = DB::table('members')->where('remember_token', $remember_token)->first();

        if ($member === null) {
            Auth::guard('member')->logout();
            return redirect()->route('domewing.auth.login')->withErrors(['email' => 'You must login to access'])->withInput();
        }

        $existingEmail = DB::table('members')->where('email', $request->input('email'))->where('id', '!=', $member->id)->first();

        if ($existingEmail) {
            return response()->json(['errors' => ['email' => 'Email already exists']], 422);
        }

        $update = DB::table('members')
            ->where('id', $member->id)
            ->update([
                'title' => $request->input('title'),
                'first_name' => $request->input('fname'),
                'last_name' => $request->input('lname'),
                'email' => $request->input('email'),
                'phone_code' => $request->input('phoneCodeHidden'),
                'phone_number' => $request->input('phoneNumber'),
                'username' => $request->input('username'),
                'updated_at' => now(),
            ]);

        if ($update) {
            $data = [
                'status' => 1,
                'icon' => 'success',
                'return' => 'Information Updated.',
            ];
        } else {
            $data = [
                'status' => -1,
                'icon' => 'error',
                'title' => 'Opps',
                'return' => 'Failed to update information.',
            ];
        }

        return $data;
    }


    public function showToShip() {
        return view('domewing.to_ship');
    }

    public function showToReceive() {
        return view('domewing.to_receive');
    }

    public function showToRate() {
        return view('domewing.to_rate');
    }

    public function showPurchaseHistory() {
        return view('domewing.purchase_history');
    }

    public function showWishlist() {
        return view('domewing.wishlist');
    }
}
