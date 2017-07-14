<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/3/26
 * Time: 下午1:47
 */

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\JWTAuth;

class UserController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
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
            'entity_type_id' => 'required|integer',
            'email' => 'email'
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