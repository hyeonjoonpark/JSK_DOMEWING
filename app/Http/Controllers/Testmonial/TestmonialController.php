<?php

namespace App\Http\Controllers\Testmonial;

use App\Http\Controllers\BusinessPageController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TestmonialController extends Controller
{
    private $bpc;
    public function __construct()
    {
        $this->bpc = new BusinessPageController();
    }
    public function index(Request $request)
    {
        $testmonials = $this->bpc->getAllTestimonials();
        return view('admin/testmonials', [
            'testmonials' => $testmonials
        ]);
    }
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|min:13',
            'messageBy' => 'required|min:2|max:10',
            'createdAt' => 'required|date',
            'rememberToken' => 'required'
        ], [
            'message' => '제품후기 내용은 최소 2글자 이상이어야 합니다.',
            'messageBy' => '작성자명은 최소 2글자, 최대 10글자입니다.',
            'createdAt' => '작성시간을 선택해주십시오.',
            'rememberToken' => '로그인 세션이 만료되었습니다. 다시 로그인해 주십시오.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'return' => $validator->messages()
            ];
        }
        $message = $request->message;
        $messageBy = $request->messageBy;
        $createdAt = $request->createdAt;
        return $this->addTestmonial($message, $messageBy, $createdAt);
    }
    public function edt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|min:13',
            'messageBy' => 'required|min:2|max:10',
            'createdAt' => 'required|date',
            'testmonialId' => 'required|integer',
            'rememberToken' => 'required'
        ], [
            'message' => '제품후기 내용은 최소 2글자 이상이어야 합니다.',
            'testmonialId' => '잘못된 접근입니다.',
            'messageBy' => '작성자명은 최소 2글자, 최대 10글자입니다.',
            'createdAt' => '작성시간을 선택해주십시오.',
            'rememberToken' => '로그인 세션이 만료되었습니다. 다시 로그인해 주십시오.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'return' => $validator->messages()
            ];
        }
        $message = $request->message;
        $messageBy = $request->messageBy;
        $createdAt = $request->createdAt;
        $testmonialId = $request->testmonialId;
        return $this->edtTestmonial($message, $messageBy, $createdAt, $testmonialId);
    }
    private function edtTestmonial($message, $messageBy, $createdAt, $testmonialId)
    {
        try {
            DB::table('testimonial')
                ->where('id', $testmonialId)
                ->update([
                    'message' => $message,
                    'message_by' => $messageBy,
                    'created_at' => $createdAt
                ]);
            return [
                'status' => true,
                'return' => "제품후기를 성공적으로 수정했습니다."
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => [
                    'message' => "제품후기를 데이터베이스에 수정하는 과정에서 오류가 발생했습니다.",
                    'error' => $e->getMessage()
                ]
            ];
        }
    }
    private function addTestmonial($message, $messageBy, $createdAt)
    {
        try {
            DB::table('testimonial')
                ->insert([
                    'message' => $message,
                    'message_by' => $messageBy,
                    'created_at' => $createdAt
                ]);
            return [
                'status' => true,
                'return' => "제품후기를 성공적으로 추가했습니다."
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => [
                    'message' => "제품후기를 데이터베이스에 입력하는 과정에서 오류가 발생했습니다.",
                    'error' => $e->getMessage()
                ]
            ];
        }
    }
    public function del(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'testmonialId' => 'required|integer',
            'rememberToken' => 'required'
        ], [
            'testmonialId' => '잘못된 접근입니다.',
            'rememberToken' => '로그인 세션이 만료되었습니다. 다시 로그인해 주십시오.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'return' => $validator->messages()
            ];
        }
        $testmonialId = $request->testmonialId;
        return $this->delTestmonial($testmonialId);
    }
    private function delTestmonial($testmonialId)
    {
        try {
            DB::table('testimonial')
                ->where('id', $testmonialId)
                ->update([
                    'status' => 'N'
                ]);
            return [
                'status' => true,
                'return' => "제품후기를 성공적으로 삭제했습니다."
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => [
                    'message' => "제품후기를 삭제하는 과정에서 오류가 발생했습니다.",
                    'error' => $e->getMessage()
                ]
            ];
        }
    }
}
