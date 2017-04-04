<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/3/26
 * Time: 下午3:40
 */

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions;

class PassportController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function signIn(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required',
        ]);

        try {
            if (!$token = $this->jwt->attempt($request->only('email', 'password'))) {
                return response()->json(['unauthorized'], 401);
            }
        } catch (Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], 500);
        } catch (Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], 500);
        } catch (Exceptions\JWTException $e) {
            return response()->json(['token_absent' => $e->getMessage()], 500);
        }

        $user = $this->jwt->user();

        $user['last_login'] = new \DateTime();

        $user->save();

        return response()->json(compact('token'));
    }

    public function signUp(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required',
            'name' => 'required|max:255'
        ]);

        $userInfo = $request->only('email', 'password', 'name');

        try {
            $user = User::create($userInfo);
        } catch (Exception $exception) {
            return response()->json(['error' => 'User already exists.'], 500);
        }

        $token = $this->jwt->fromUser($user);

        return response()->json(compact('token'), 201);
    }
}