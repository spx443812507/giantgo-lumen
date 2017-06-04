<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/3
 * Time: 下午8:17
 */

namespace App\Http\Controllers;

use App\Models\OAuthUser;
use App\Models\User;
use EasyWeChat\Foundation\Application;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class OAuthController extends Controller
{
    protected $jwt;

    protected $weChat;

    public function __construct(JWTAuth $jwt, Application $weChat)
    {
        $this->jwt = $jwt;
        $this->weChat = $weChat;
    }

    public function weChatLogin(Request $request, $appId)
    {
        $returnUrl = $request->input('returnUrl');

        $this->weChat['config']->set('oauth.scopes', ['snsapi_login']);

        $this->weChat['config']->set('oauth.callback', '/api/oauth/applications/' . $appId . '/wechat/callback?returnUrl=' . $returnUrl);

        $oauth = $this->weChat->oauth;

        return $oauth->stateless()->redirect();
    }

    public function weChatCallback(Request $request)
    {
        //是否已经绑定系统用户
        $isBind = false;

        $returnUrl = $request->input('returnUrl');

        $oauth = $this->weChat->oauth;

        $user = $oauth->user();

        $oAuthUser = OAuthUser::where('open_id', $user->getId())->first();

        //如果没有该微信用户
        if (empty($oAuthUser)) {
            $oAuthUser = OAuthUser::create([
                'open_id' => $user->getId(),
                'name' => $user->getName(),
                'nickname' => $user->getNickname(),
                'avatar' => $user->getAvatar(),
                'email' => $user->getEmail(),
                'provider' => 'wechat'
            ]);
        } else {
            $oAuthUser['last_auth'] = new \DateTime();
            $oAuthUser->save();
        }

        if ($oAuthUser->user) {
            $user = new User($oAuthUser->user->toArray());
            $user['id'] = $oAuthUser->user->id;
            $token = $this->jwt->fromUser($user);
            $isBind = true;
        } else {
            $token = $this->jwt->fromUser($oAuthUser);
        }

        if (empty($returnUrl)) {
            return response()->json(compact('token'));
        } else {
            $queries = array_except($request->query(), ['code', 'state', 'returnUrl']);

            $queries[$isBind ? 'token' : 'verify'] = $token;

            return redirect()->to($returnUrl . '?' . http_build_query($queries));
        }
    }
}