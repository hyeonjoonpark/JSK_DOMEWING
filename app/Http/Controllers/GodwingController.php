<?php

namespace App\Http\Controllers;

use App\Models\Godwing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GodwingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $validator = Validator::make($request->all(), [
            'vendorId' => ['required', 'exists:vendors,id']
        ]);
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
    public function update(Request $request, Godwing $godwing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Godwing $godwing)
    {
        //
    }
}
