<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class VendorController extends Controller
{
    public function getVendor($vendorID)
    {
        try {
            $vendor = DB::table('vendors')
                ->where('id', $vendorID)
                ->first();
            return $vendor;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
