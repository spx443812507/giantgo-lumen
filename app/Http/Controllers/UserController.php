<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/3/26
 * Time: 下午1:47
 */

namespace App\Http\Controllers;

use App\Models\EAV\Attribute;
use App\Models\EAV\Factories\EntityFactory;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions;

class UserController extends Controller
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

    public function get($id)
    {
        return $id;
    }

    public function getList(Request $request)
    {
        return response()->json(User::all());
    }

    public function updateUser(Request $request)
    {
        $this->validate($request, [
            'entity_type_id' => 'required|integer',
            'user_id' => 'required|integer'
        ]);

        $entityTypeId = $request->input('entity_type_id');

        $userId = $request->input('user_id');

        $userClass = EntityFactory::getEntity($entityTypeId);

        $user = $userClass::find($userId);

        return response()->json($user->name()->get());
    }
}