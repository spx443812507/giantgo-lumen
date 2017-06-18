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

    public function createAttributes(Request $request)
    {
        $this->validate($request, [
            'entity_type_id' => 'required|integer',
            'attributes' => 'required|array',
            'attributes.*.attribute_code' => 'required|unique:attributes,attribute_code',
            'attributes.*.frontend_label' => 'required',
            'attributes.*.frontend_input' => 'required',
        ]);

        $result = [];

        $attributes = $request->input('attributes');

        for ($index = 0; $index < count($attributes); $index++) {
            $result[] = Attribute::create([
                'entity_type_id' => $request->input('entity_type_id'),
                'attribute_code' => $attributes[$index]['attribute_code'],
                'frontend_input' => $attributes[$index]['frontend_input'],
                'frontend_model' => empty($attributes[$index]['frontend_model']) ? '' : $attributes[$index]['frontend_model'],
                'frontend_label' => $attributes[$index]['frontend_label'],
                'frontend_class' => empty($attributes[$index]['frontend_class']) ? '' : $attributes[$index]['frontend_class'],
                'is_required' => $attributes[$index]['is_required'],
                'is_user_defined' => false,
                'is_unique' => $attributes[$index]['is_unique'],
                'default_value' => $attributes[$index]['default_value'],
                'description' => $attributes[$index]['description'],
            ]);
        }

        return response()->json($result, 200);
    }
}