<?php
namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    public function index()
    {
        DB::table('users')->where('id', 15)->update([
            'password' => bcrypt('Tjddlf88!@')
        ]);
    }
}