<?php

namespace App\Http\Controllers\Nalmeokwings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Nalmeokwings\Services\NalmeokwingCreateService;
use App\Http\Controllers\Nalmeokwings\Services\NalmeokwingStoreService;
use Illuminate\Http\Request;

class NalmeokwingController extends Controller
{
    public function create()
    {
        $nss = new NalmeokwingCreateService();
        $data = $nss->main();
        return view('admin.nalmeokwing_create', [
            'data' => $data
        ]);
    }
    public function store(Request $request)
    {
        $nss = new NalmeokwingStoreService();
        return $nss->main($request);
    }
}
