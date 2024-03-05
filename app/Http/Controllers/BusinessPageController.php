<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use DateTime;
use Exception;

class BusinessPageController extends Controller
{
    public function showBusinessPage(Request $request)
    {
        $partners = $this->getAllPartner();
        $testimonials = $this->getAllTestimonials();
        $phoneCodes = $this->getAllPhoneCode();
        return view('business_page.index', ['partners' => $partners, 'testimonials' => $testimonials, 'phoneCodes' => $phoneCodes]);
    }

    public function getAllPartner()
    {
        $images = DB::table('partnership')->where('status', 'Y')->inRandomOrder()->get();
        return $images;
    }

    public function getAllTestimonials()
    {
        $testimonials = DB::table('testimonial')->where('status', 'Y')->get();

        foreach ($testimonials as $record) {
            $dateTime = new DateTime($record->created_at);

            // Format the date
            $record->formatted_date = $dateTime->format('Y년 m월 d일, ');
            $record->formatted_date .= ($dateTime->format('A') === 'AM' ? '오전' : '오후') . $dateTime->format('h시 i분');
        }

        return $testimonials;
    }

    public function getAllPhoneCode()
    {
        $phoneCodes = DB::table('country_codes')->where('status', 'Y')->get();
        return $phoneCodes;
    }

    public function submitContactUs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|min:2|max:255',
            'phoneCode' => 'required',
            'phoneNumber' => 'required|min:6|max:50|regex:/^[0-9]+$/',
            'message' => 'required',
        ], [
            'required' => '이 필드는 필수입니다.',
            'phoneCode.required' => '국가 코드를 선택해주세요.',
            'min' => '이 필드는 최소 :min 글자 이상이어야 합니다.',
            'max' => '이 필드는 최대 :max 글자까지만 허용됩니다.',
            'regex' => '이 필드에는 숫자만 입력할 수 있습니다.',
            'email' => '유효한 이메일 주소를 입력해주세요.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $name = trim($request->input('name'));
        $email = trim($request->input('email'));
        $phoneCode = trim($request->input('phoneCode'));
        $phoneNumber = trim($request->input('phoneNumber'));
        $message = trim($request->input('message'));

        $checkPhoneCode = DB::table('country_codes')->where('id', $phoneCode)->where('status', 'Y')->first();

        if (!$checkPhoneCode) {
            return response()->json(['errors' => ['phoneCode' => ['전화번호가 존재하지 않습니다']]], 422);
        }

        try {
            $insert = DB::table('sellwing_contact_us')->insert([
                'name' => $name,
                'email' => $email,
                'phone_code' => $phoneCode,
                'phone_number' => $phoneNumber,
                'message' => $message,
                'created_at' => now(),
            ]);

            if ($insert) {
                $data = [
                    'status' => 1
                ];
            } else {
                $data = [
                    'status' => -1
                ];
            }
        } catch (Exception $e) {
            $data = [
                'status' => -1
            ];
        }

        return $data;
    }
}
