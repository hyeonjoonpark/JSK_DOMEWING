<?php

namespace App\Http\Controllers\Nalmeokwings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Nalmeokwings\Services\NalmeokwingCreateService;
use App\Http\Controllers\Nalmeokwings\Services\NalmeokwingExtractService;
use App\Http\Controllers\Nalmeokwings\Services\NalmeokwingIndexService;
use App\Http\Controllers\Nalmeokwings\Services\NalmeokwingOrderService;
use App\Http\Controllers\Nalmeokwings\Services\NalmeokwingStoreService;
use Illuminate\Http\Request;

class NalmeokwingController extends Controller
{
    public function index()
    {
        $nis = new NalmeokwingIndexService();
        $data = $nis->main();
        return view('admin.nalmeokwing_index', [
            'data' => $data
        ]);
    }
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
    public function order()
    {
        $nos = new NalmeokwingOrderService();
        $data = $nos->main();
        return view('admin.nalmeokwing_order', [
            'data' => $data
        ]);
    }
    public function extract(Request $request)
    {
        $nes = new NalmeokwingExtractService();
        return $nes->main($request);
    }
}
