<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/4
 * Time: 下午8:45
 */

namespace App\Http\Controllers;


use App\Models\SocialAccount;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;

class SocialAccountController extends Controller
{
    public function me(Request $request)
    {
        $this->validate($request, [
            'verify' => 'required',
        ]);

        try {
            $payload = JWT::decode($request->input('verify'), env('JWT_SECRET'), array('HS256'));
        } catch (ExpiredException $e) {
            return response()->json(['error' => 'token_expired'], 500);
        } catch (SignatureInvalidException $e) {
            return response()->json(['error' => 'token_invalid'], 500);
        }

        $oAuthUser = SocialAccount::find($payload->sub);

        if (empty($oAuthUser)) {
            return response()->json(['error' => 'social_account_not_exists'], 500);
        }

        return response()->json($oAuthUser);
    }
}