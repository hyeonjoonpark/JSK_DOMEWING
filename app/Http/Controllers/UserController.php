<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class UserController extends Controller
{
    public function getUser($remember_token)
    {
        try {
            $user = DB::table('users')
                ->where('remember_token', $remember_token)
                ->first();
            return $user;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    public function validateRememberToken($rememberToken)
    {
        $user = DB::table('users')
            ->where('remember_token', $rememberToken)
            ->where('is_active', 'ACTIVE')
            ->exists();
        return $user;
    }
}
