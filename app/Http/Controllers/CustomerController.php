<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/12
 * Time: 下午5:26
 */

namespace app\Http\Controllers;


use App\Models\Customer;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class CustomerController
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function getList(Request $request)
    {
        return response()->json(Customer::all());
    }
}