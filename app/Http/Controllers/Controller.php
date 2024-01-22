<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, DB;
    public function getVendor($vendorID)
    {
        try {
            $vendor = DB::table('vendors')
                ->where('is_active', 'ACTIVE')
                ->where('id', $vendorID)
                ->first();
            if ($vendor == '') {
                return [
                    'status' => false,
                    'return' => '유효한 업체가 아닙니다.'
                ];
            }
            return [
                'status' => true,
                'return' => $vendor
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage()
            ];
        }
    }
}
