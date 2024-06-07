<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function handle($partnerId, $type, $data, $readAt, $createdAt, $updatedAt)
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
}
