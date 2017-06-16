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

    public function getAttributes(Request $request)
    {
        $userClass = EntityFactory::getEntity(1);

        $user = new $userClass();

        return response()->json($user->attributes());
    }

    public function addAttributes(Request $request)
    {
        $attributes = $request->input('attributes');

        for ($index = 0; $index < count($attributes); $index++) {
            Attribute::create([
                'entity_type_id' => 1,
                'attribute_code' => 'name',
                'attribute_model' => '',
                'backend_model' => '',
                'backend_type' => 'varchar',
                'backend_table' => '',
                'frontend_model' => '',
                'frontend_input' => 'text',
                'frontend_label' => '姓名',
                'frontend_class' => '',
                'is_required' => true,
                'is_user_defined' => false,
                'is_unique' => false,
                'default' => '张三',
                'description' => '该字段记录用户姓名',
            ]);
        }
    }
}