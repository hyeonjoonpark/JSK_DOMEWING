<?php

namespace App\Http\Controllers;

use App\Mail\ContactAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactUsController extends Controller
{
    public function index()
    {
        $contacts = DB::table('sellwing_contact_us')
            ->orderByRaw("CASE WHEN status = 'PENDING' THEN 0 ELSE 1 END")
            ->orderBy('created_at')
            ->get();
        return view('admin.contact_us', [
            'contacts' => $contacts
        ]);
    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:20',
            'email' => 'required|email',
            'phoneNumber' => 'required|min:10|max:11',
            'message' => 'required'
        ], [
            'name' => '이름은 2글자 이상, 20글자 이하여야 합니다.',
            'email' => '유효한 이메일 주소를 입력해주세요.',
            'phoneNumber' => '연락처는 10자리 이상, 11자리 이하여야 합니다.',
            'message' => '문의 내용을 입력해주세요.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
        return $this->store($request);
    }
    private function store($contact)
    {
        try {
            DB::table('sellwing_contact_us')
                ->insert([
                    'name' => $contact->name,
                    'email' => $contact->email,
                    'phone_number' => $contact->phoneNumber,
                    'message' => $contact->message
                ]);
            return [
                'status' => true,
                'message' => '문의 내용이 접수되었습니다. 입력해주신 이메일로 답변을 보내드리겠습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '문의 내용을 접수하는 과정에서 오류가 발생했습니다. 다음에 다시 시도해주십시오.',
                'error' => $e->getMessage()
            ];
        }
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contactId' => 'required|exists:sellwing_contact_us,id',
            'answer' => 'required'
        ], [
            'contactId.required' => '유효한 문의가 아닙니다.',
            'answer.required' => '답변을 입력해주세요.'
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }

        $updateResult = $this->updateContact($request);
        if (!$updateResult['status']) {
            return $updateResult;
        }

        $contact = DB::table('sellwing_contact_us')
            ->where('id', $request->contactId)
            ->first();

        return $this->sendAnswer($contact);
    }

    private function updateContact($contact)
    {
        try {
            DB::table('sellwing_contact_us')
                ->where('id', $contact->contactId)
                ->update([
                    'answer' => $contact->answer,
                    'status' => 'ANSWERED'
                ]);
            return [
                'status' => true
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '문의 답변을 저장하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    private function sendAnswer($contact)
    {
        try {
            Mail::to($contact->email)->send(new ContactAnswer($contact));
            return [
                'status' => true,
                'message' => '문의 내용에 대한 답변을 성공적으로 전송했습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '문의 답변을 메일로 전송하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
}
