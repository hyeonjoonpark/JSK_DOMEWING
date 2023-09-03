<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function register()
    {
        $response = $this->showVendorList();
        $status = $response['status'];
        if ($status == 1) {
            $vendors = $response['return'];
            return view('auth/register', ['vendors' => $vendors]);
        } else {
            return view('auth/login');
        }
    }
    public function showVendorList()
    {
        try {
            $vendors = DB::table('vendors')->whereIn('is_active', ['active'])->get();
            $data['status'] = 1;
            $data['return'] = $vendors;
        } catch (Exception $e) {
            $data['status'] = -1;
            $data['return'] = $e->getMessage();
        }
        return $data;
    }
}