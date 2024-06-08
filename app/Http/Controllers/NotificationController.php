<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function handle($partnerId, $type, $data)
    {
        try {
            DB::table('notifications')
                ->insert([
                    'partner_id' => $partnerId,
                    'type' => $type,
                    'data' => $data
                ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function readAll(Request $request)
    {
        $partnerId = DB::table('partners')
            ->where('api_token', $request->apiToken)
            ->value('id');
        try {
            DB::table('notifications')
                ->where('read_at', null)
                ->where('partner_id', $partnerId)
                ->update([
                    'read_at' => now()
                ]);
            return [
                'status' => true,
                'message' => '모든 알림을 읽음 처리했습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '알림 읽음 처리하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
}
