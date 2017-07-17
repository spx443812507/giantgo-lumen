<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/3/26
 * Time: 下午1:47
 */

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Exceptions;
use Tymon\JWTAuth\JWTAuth;

class UserController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
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


    public function get(Request $request, $userId)
    {
        $user = User::find($userId);

        if (empty($user)) {
            return response()->json(['error' => 'user_not_exists'], 500);
        }

        return response()->json($user);
    }

    public function getList(Request $request)
    {
        $users = User::all();

        return response()->json($users);
    }

    public function updateUser(Request $request, $userId)
    {
        $this->validate($request, [
            'email' => 'require|email'
        ]);

        $userInfo = $request->except('id');

        $entityTypeId = $request->input('entity_type_id');

        $validator = Validator::make($userInfo, [
            'email' => [
                Rule::unique('users')->where(function ($query) use ($entityTypeId) {
                    $query->where('entity_type_id', $entityTypeId);
                })->ignore($userId),
            ],
            'mobile' => [
                Rule::unique('users')->where(function ($query) use ($entityTypeId) {
                    $query->where('entity_type_id', $entityTypeId);
                })->ignore($userId),
            ]
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userClass = Entity::getEntity($entityTypeId);

        $user = $userClass::find($userId);

        if (empty($user)) {
            return response()->json(['error' => 'user_not_exists'], 500);
        }

        $user->fill($userInfo);

        $user->save();

        return response()->json($user);
    }
}