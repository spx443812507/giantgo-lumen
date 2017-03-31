<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/3/26
 * Time: 下午1:47
 */

namespace App\Http\Controllers;


class UserController extends Controller
{
    public function get($id)
    {
        return $id;
    }

    public function getList()
    {
        return var_dump([1, 2, 3]);
    }
}