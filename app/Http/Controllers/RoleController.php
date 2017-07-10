<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/3/26
 * Time: 下午1:47
 */

namespace App\Http\Controllers;

use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class RoleController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function createRole(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles',
            'display_name' => 'required',
            'description' => 'required'
        ]);

        $roleInfo = $request->only('name', 'display_name', 'description');

        try {
            $role = new Role();
            $role->name = $roleInfo['name'];
            $role->display_name = $roleInfo['display_name'];
            $role->description = $roleInfo['description'];
            $role->save();
        } catch (Exception $exception) {
            return response()->json(['error' => 'create_role_error'], 500);
        }

        return response()->json($role, 200);
    }

    public function getRole(Request $request, $roleId)
    {
        $role = Role::find($roleId);

        if (empty($role)) {
            return response()->json(['error' => 'role_not_exists'], 400);
        }

        return response()->json($role, 200);
    }
}