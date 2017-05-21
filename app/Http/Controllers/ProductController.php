<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/5/21
 * Time: 上午10:56
 */

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class ProductController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|max:255',
            'description' => 'required|max:255',
            'price' => 'numeric'
        ]);

        try {
            $user = $this->jwt->user();

            $product = new Product($request->only('title', 'description', 'price'));

            $product = $user->products()->save($product);
        } catch (Exception $exception) {
            return response()->json(['error' => 'create_error'], 500);
        }

        return response()->json($product, 201);
    }
}