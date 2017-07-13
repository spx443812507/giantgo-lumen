<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/3/26
 * Time: 下午3:40
 */

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions;
use Tymon\JWTAuth\JWTAuth;

class PassportController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function me()
    {
        try {
            $user = $this->jwt->parseToken()->toUser();

            if (!$user) {
                return response()->json(['error' => 'unauthorized'], 401);
            }
        } catch (Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'token_expired'], 500);
        } catch (Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'token_invalid'], 500);
        } catch (Exceptions\JWTException $e) {
            return response()->json(['error' => 'token_absent'], 500);
        }

        return response()->json($user);
    }

    public function signIn(Request $request)
    {
        $this->validate($request, [
            'password' => 'required',
            'email' => 'email|required_without:mobile'
        ]);

        $email = $request->input('email');
        $mobile = $request->input('mobile');

        $credential = [
            'password' => $request->input('password')
        ];

        if (isset($email)) {
            $credential['email'] = $request->input('email');
        } else if (isset($mobile)) {
            $credential['mobile'] = $request->input('mobile');
        }

        try {
            if (!$token = $this->jwt->attempt($credential)) {
                return response()->json(['error' => 'username_or_password_error'], 404);
            }
        } catch (Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'token_expired'], 500);
        } catch (Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'token_invalid'], 500);
        } catch (Exceptions\JWTException $e) {
            return response()->json(['error' => 'token_absent'], 500);
        }

        $user = $this->jwt->user();

        $user['last_login'] = new \DateTime();

        $user->save();

        return response()->json(compact('token'));
    }

    public function signUp(Request $request)
    {
        $this->validate($request, [
            'email' => 'email|max:255|unique:users',
            'mobile' => 'max:255|unique:users',
            'password' => 'required'
        ]);

        $userInfo = $request->all();

        try {
            $user = new User;

            $user->fill($userInfo);

            $user->save();

            $customerRole = Role::where('name', 'owner')->first();

            $user->attachRole($customerRole);
        } catch (Exception $exception) {
            return response()->json(['error' => 'create_user_fail'], 500);
        }

        $token = $this->jwt->fromUser($user);

        return response()->json(compact('token'), 201);
    }
}