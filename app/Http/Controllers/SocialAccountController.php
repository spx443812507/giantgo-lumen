<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/4
 * Time: 下午8:45
 */

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;

class SocialAccountController extends Controller
{
    public function get(Request $request)
    {
        $this->validate($request, [
            'verify' => 'required',
        ]);

        try {
            $payload = JWT::decode($request->input('verify'), env('JWT_SECRET'), array('HS256'));
        } catch (ExpiredException $e) {
            throw new Exception('token_expired');
        } catch (SignatureInvalidException $e) {
            throw new Exception('token_invalid');
        }

        $oAuthUser = SocialAccount::find($payload->sub);

        if (empty($oAuthUser)) {
            throw new Exception('social_account_not_exists');
        }

        return response()->json($oAuthUser);
    }
}