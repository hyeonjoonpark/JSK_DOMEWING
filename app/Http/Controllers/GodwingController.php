<?php

namespace App\Http\Controllers;

use App\Models\Godwing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GodwingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gis = new GodwingIndexService();
        return $gis->main();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Godwing $godwing)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Godwing $godwing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $gus = new GodwingUpdateService();
        return $gus->main($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($vendorId)
    {
        try {
            DB::table('vendors')
                ->where('id', (int)$vendorId)
                ->update([
                    'is_godwing' => 0
                ]);
            return [
                'status' => true,
                'message' => "해당 업체를 갓윙 목록으로부터 제외했습니다."
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => "해당 업체를 갓윙 목록으로부터 제외하는 과정에서 오류가 발생했습니다.",
                'error' => $e->getMessage()
            ];
        }
    }
}
