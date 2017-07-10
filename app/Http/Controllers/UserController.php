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
use App\Models\EAV\Factories\EntityFactory;
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

        $entityTypeId = $user->entity_type_id;

        $user->bootEntityAttribute($entityTypeId);

        $relations = $user->getEntityAttributeRelations();

        $user->load(array_keys($relations));

        return response()->json($user);
    }

    public function getList(Request $request)
    {
        $entityTypeId = $request->input('entity_type_id') ?: 1;

        $userClass = EntityFactory::getEntity($entityTypeId);

        $users = $userClass::all();

        return response()->json($users);
    }

    public function updateUser(Request $request)
    {
        $this->validate($request, [
            'entity_type_id' => 'required|integer',
            'users.user_id' => 'required|integer'
        ]);

        $userData = $request->input('users');

        $validator = Validator::make($userData, [
            'email' => [
                Rule::unique('users')->ignore($userData['user_id']),
            ],
            'mobile' => [
                Rule::unique('users')->ignore($userData['user_id']),
            ]
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $entityTypeId = $request->input('entity_type_id');

        $userInfo = $request->input('users');

        $userClass = EntityFactory::getEntity($entityTypeId);

        $user = $userClass::find($userInfo['user_id']);

        if (empty($user)) {
            return response()->json(['error' => 'user_not_exists'], 500);
        }

        $user->fill($userInfo);

        $user->save();

        return response()->json($user);
    }
}