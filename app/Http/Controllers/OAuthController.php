<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/3
 * Time: 下午8:17
 */

namespace App\Http\Controllers;

use EasyWeChat\Foundation\Application;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTFactory;
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
        $open_platform = $this->weChat->open_platform;

        $app = $open_platform->createAuthorizerApplication($appId);

        $app['config']->set('oauth.scopes', [$request->input('scope')]);

        $app['config']->set('oauth.callback', '/api/wechat/callback');

        $oauth = $app->oauth;

        return $oauth->redirect();
    }

    public function weChatCallback()
    {
        $oauth = $this->weChat->oauth;

        $user = $oauth->user();

        $payload = JWTFactory::make(['open_id' => $user['id']]);

        $token = JWTAuth::encode($payload);

        return response()->json(compact('token'));
    }
}