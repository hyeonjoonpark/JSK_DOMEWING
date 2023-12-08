<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function showCheckoutPage(Request $request, $id){

        $verifyOrder = $this->verifyOrder($id);
        $verifyMember = $this->verifyMember($id);

        if(!$verifyOrder){
            return redirect('/domewing')->with('error', 'Checkout Not Found');
        }else if (!$verifyMember){
            return redirect('/domewing')->with('error', 'Access Denied');
        }else{
            $getOrder = $this->getOrder($id);
            $getUserDetails = $this->getUserDetails();

            return view('domewing.checkout', ['getOrder' => $getOrder, 'getUserDetails'=> $getUserDetails]);
        }
    }

    public function getMargin(){
        $margin = DB::table('domewing_margin_rate')
                    ->where('id', 1)
                    ->first()
                    ->rate;

        return $margin;
    }

    //verify existing order
    public function verifyOrder($id){
        $verifyOrder = DB::table('order')
                    ->where('order_id', $id)
                    ->where('status', 'Y')
                    ->first();

        if (!$verifyOrder){
            return false;
        }

        return true;
    }

    //verify whether the order belongs to the authenticated user
    public function verifyMember($id){
        $orderMember = DB::table('order')
                    ->join('members', 'order.user_id', '=', 'members.id')
                    ->where('order.order_id', $id)
                    ->where('order.status', 'Y')
                    ->select('members.remember_token')
                    ->first();

        $member = Auth::guard('member')->user();

        if ($orderMember !== null && $member->remember_token !== $orderMember->remember_token) {
            return false;
        }

        return true;
    }

    //if both verified, retrieve the checkout details
    public function getOrder($id){

        $getOrder = DB::table('order_items')
                    ->join('uploaded_products', 'order_items.product_id', '=', 'uploaded_products.id')
                    ->join('collected_products','uploaded_products.productId','=', 'collected_products.id')
                    ->join('users', 'collected_products.userId', '=', 'users.id')
                    ->where('order_items.order_id', $id)
                    ->where('order_items.status', 'Y')
                    ->select('order_items.*',
                                'users.company as supplier_name',
                                'uploaded_products.newProductName as productName',
                                'uploaded_products.newImageHref as image',
                                'collected_products.productPrice as price',
                                'collected_products.shippingCost as shippingCost')
                    ->get();

        $margin = $this->getMargin();

        foreach ($getOrder as $item) {
            // Calculate the new price by multiplying productPrice with margin
            $newPrice = $item->price * ($margin / 100 + 1);

            // Update the price in the shopping cart item
            $item->price = $newPrice;
        }

        return $getOrder;
    }

    //load user profile for the delivery details
    public function getUserDetails(){
        $member = Auth::guard('member')->user();

        $getUserDetails = DB::table('members')->where('remember_token', $member->remember_token)->where('is_active','ACTIVE')->first();

        return $getUserDetails;
    }

    public function checkoutOrder(Request $request){

        $validator = Validator::make($request->all(),[
            'orderId' => 'required',
            'paymentMethod' => 'required',
            'remember_token' => 'required',
            'contactName' => 'required',
            'phoneCodeHidden' => 'required',
            'phoneNumber' => 'required|min:6',
            'email' => 'required|email',
            'street' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zipCode' => 'required|regex:/^\d{5}(-\d{4})?$/',
            'country' => 'required',
        ], [
            'required' => 'This field is required.',
            'phoneNumber.min' => 'Phone number must have at least 6 numbers.',
            'email.email' => 'Please provide a valid email address.',
            'zipCode.regex' => 'Please provide a valid zipcode.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $orderId = $request->input('orderId');
        $paymentMethod = $request->input('paymentMethod');
        $remember_token = $request->input('remember_token');
        $contactName = $request->input('contactName');
        $phoneCodeHidden = $request->input('phoneCodeHidden');
        $phoneNumber = $request->input('phoneNumber');
        $email = $request->input('email');
        $street = $request->input('street');
        $city = $request->input('city');
        $state = $request->input('state');
        $zipCode = $request->input('zipCode');
        $country = $request->input('country');

        //verify order and member
        $verifyOrder = $this->verifyOrder($orderId);
        $verifyMember = $this->verifyMember($remember_token);

        if(!$verifyOrder){
            return redirect('/domewing')->with('error', 'Checkout Not Found');
        }else if (!$verifyMember){
            return redirect('/domewing')->with('error', 'Access Denied');
        }

        //if nothing wrong, checkout order
        $prefix = 'TRS'; // Prefix for your order ID
        $timestamp = Carbon::now()->format('YmdHis'); // Current timestamp formatted as 'YmdHis'

        $transactionId = $prefix . $timestamp;

        $member = DB::table('members')->where('remember_token', $remember_token)->where('is_active','ACTIVE')->first();

        try{

            //update order_items table with current price, else if the product price changes then hard to find original price
            $getAllItems = DB::table('order_items')
                            ->join('uploaded_products', 'order_items.product_id', '=', 'uploaded_products.id')
                            ->join('collected_products','uploaded_products.productId','=','collected_products.id')
                            ->where('order_items.order_id',$orderId)
                            ->select('order_items.product_id', 'collected_products.productPrice', 'collected_products.shippingCost')
                            ->get();

            $margin = $this->getMargin();

            foreach ($getAllItems as $item) {
                // Calculate the new price by multiplying productPrice with margin
                $newPrice = $item->productPrice * ($margin / 100 + 1);

                // Update the price in the shopping cart item
                $item->productPrice = $newPrice;
            }

            foreach ($getAllItems as $item) {
                DB::table('order_items')
                    ->where('product_id', $item->product_id)
                    ->where('order_id', $orderId)
                    ->update([
                        'price_at' => $item->productPrice,
                        'shipping_at' => $item->shippingCost
                    ]);

                DB::table('shopping_cart')
                    ->where('product_id', $item->product_id)
                    ->where('user_id', $member->id)
                    ->where('is_Active', 'ACTIVE')
                    ->update(['is_Active' => 'INACTIVE','updated_at' => now()]);
            }

            $transaction = DB::table('transaction_order')->insert([
                'order_id' => $orderId,
                'user_id' => $member->id,
                'status' => 'PAID',
                'payment_method' => $paymentMethod,
                'created_at' => now(),
                'transaction_id' => $transactionId,
            ]);

            $delivery = DB::table('delivery_details')->insert([
                'transaction_id' => $transactionId,
                'contact_name' => $contactName,
                'phone_code' => $phoneCodeHidden,
                'phone_number' => $phoneNumber,
                'email' => $email,
                'street' => $street,
                'city' => $city,
                'state' => $state,
                'zipcode' => $zipCode,
                'country' => $country,
                'created_at' => now(),
            ]);

            if($transaction && $delivery){
                $data = [
                    'status' => 1,
                    'icon' => 'success',
                    'return' => 'Thank You for Your Order.'
                ];
            }else{
                $data = [
                    'status' => -1,
                    'icon' => 'danger',
                    'title' => 'Opps',
                    'return' => 'Something Went Wrong. Please Try Again Later.'
                ];
            }

        }catch(Exception $e){
            $data = [
                'status' => -1,
                'icon' => 'danger',
                'title' => 'Error Occured',
                'return' => 'Something Went Wrong. Please Try Again Later.'
            ];
        }

        return $data;
    }
}
