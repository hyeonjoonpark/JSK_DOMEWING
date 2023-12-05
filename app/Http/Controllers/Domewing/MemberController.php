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
                'title' => 'SUCCESS',
                'return' => 'Profile Updated Successfully.',
            ];
        } else {
            $data = [
                'status' => -1,
                'title' => 'ERROR',
                'return' => 'Failed to Update Information. Please Try Again Later.',
            ];
        }

        return $data;
    }

    public function getTransactionDetails(Request $request, $id){
        $transaction = DB::table('transaction_order')
                        ->join('delivery_details', 'transaction_order.transaction_id', "=", 'delivery_details.transaction_id')
                        ->where('transaction_order.transaction_id', $id)
                        ->first();

        $transaction->location = implode(', ', array_filter([
            $transaction->street,
            $transaction->city,
            $transaction->state,
            $transaction->zipcode,
            $transaction->country,
        ]));

        $transaction->contact_number = implode(' ', array_filter([
            $transaction->phone_code,
            $transaction->phone_number,
        ]));

        $items = DB::table('order_items')
                    ->join('uploaded_products','order_items.product_id','=','uploaded_products.id')
                    ->join('collected_products','uploaded_products.productId','=','collected_products.id')
                    ->where('order_id', $transaction->order_id)
                    ->select('order_items.*', 'collected_products.productName')
                    ->get();

        $total = 0;

        foreach ($items as $item){
            $total += $item->price_at * $item->quantity + $item->shipping_at;
            $item->price_at = "KRW " . number_format($item->price_at,2);
            $item->shipping_at = "KRW " . number_format($item->shipping_at,2);
        };

        return ['transaction' => $transaction, 'items' => $items, 'total'=>"KRW " . number_format($total,2)];
    }

    public function showWishlist() {
        return view('domewing.wishlist');
    }
}
