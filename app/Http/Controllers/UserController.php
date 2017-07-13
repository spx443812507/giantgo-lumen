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


        $userClass = EntityFactory::getEntity($entityTypeId);

        $user = $userClass::find($userId);

        if (empty($user)) {
            return response()->json(['error' => 'user_not_exists'], 500);
        }

        $user->fill($userInfo);

        $user->save();

        return response()->json($user);
    }
}