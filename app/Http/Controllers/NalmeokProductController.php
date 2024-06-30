<?php

namespace App\Http\Controllers;

use App\Models\NalmeokProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NalmeokProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin/nalmeok_product_index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $npcc = new NalmeokProductCreateController();
        return $npcc->main();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $npsc = new NalmeokProductStoreController();
        return $npsc->main($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(NalmeokProduct $nalmeokProduct)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NalmeokProduct $nalmeokProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NalmeokProduct $nalmeokProduct)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NalmeokProduct $nalmeokProduct)
    {
        //
    }
}
