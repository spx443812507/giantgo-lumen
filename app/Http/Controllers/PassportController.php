<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/3/26
 * Time: 下午3:40
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class PassportController extends Controller
{
    public function signIn()
    {
        $users = DB::table('users')->get();
        return $users;
    }
}