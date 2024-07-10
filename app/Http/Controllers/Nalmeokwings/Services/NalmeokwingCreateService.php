<?php

namespace App\Http\Controllers\Nalmeokwings\Services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NalmeokwingCreateService extends Controller
{
    public function main()
    {
        $vendors = $this->getVendors();
        return [
            'vendors' => $vendors
        ];
    }
    protected function getVendors()
    {
        return DB::table('vendors')
            ->where('type', 'B2B')
            ->orderBy('name')
            ->get();
    }
}
