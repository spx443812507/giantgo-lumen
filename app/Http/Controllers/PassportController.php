<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/3/26
 * Time: 下午3:40
 */

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\User;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions;

class PassportController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    private function bindSocialAccount($user, $token)
    {
        try {
            $payload = JWT::decode($token, env('JWT_SECRET'), array('HS256'));
        } catch (ExpiredException $e) {
            return response()->json(['error' => 'token_expired'], 500);
        } catch (SignatureInvalidException $e) {
            return response()->json(['error' => 'token_invalid'], 500);
        }

        $oAuthUser = SocialAccount::find($payload->sub)->first();

        if (!empty($oAuthUser)) {
            $user->oAuthUsers()->save($oAuthUser);
        }
    }

    public function signIn(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            if (!$token = $this->jwt->attempt($request->only('email', 'password'))) {
                return response()->json(['error' => 'user_not_exists'], 404);
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

        if (!empty($request->input('verify'))) {
            $this->bindSocialAccount($user, $request->input('verify'));
        }

        return response()->json(compact('token'));
    }

    public function signUp(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required',
            'name' => 'required|max:255'
        ]);

        $userInfo = $request->only('email', 'password', 'name', 'mobile');

        try {
            $user = User::create($userInfo);
        } catch (Exception $exception) {
            return response()->json(['error' => 'user_already_exists'], 500);
        }

        if (!empty($request->input('verify'))) {
            $this->bindSocialAccount($user, $request->input('verify'));
        }

        $token = $this->jwt->fromUser($user);

        return response()->json(compact('token'), 201);
    }
}