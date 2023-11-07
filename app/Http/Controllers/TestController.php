<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function handle()
    {
        $email = "vingkongchong@gmail.com";
        $password = "12345678";
        DB::table('users')->insert([
            'name' => 'Ving Kong',
            'email' => $email,
            'password' => bcrypt($password),
            'remember_token' => Str::random(60),
            'created_at' => now(),
            'updated_at' => now(),
            'company' => 'LP MVP Sdn Bhd',
        ]);
    }
}
