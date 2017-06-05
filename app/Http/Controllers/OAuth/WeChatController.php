<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/3
 * Time: 下午8:17
 */

namespace App\Http\Controllers\OAuth;

use EasyWeChat\Foundation\Application;
use Illuminate\Http\Request;
use Overtrue\Socialite\AuthorizeFailedException;
use Tymon\JWTAuth\JWTAuth;

class WeChatController extends AbstractController
{
    protected $weChat;

    public function __construct(JWTAuth $jwt)
    {
        parent::__construct($jwt);

        $this->weChat = new Application(config('wechat'));
    }

    public function login(Request $request)
    {
        $appId = $request->input('app_id');

        $returnUrl = $request->input('return_url');

        if (isset($appId)) {
            $this->weChat['config']->set('app_id', $appId);
        }

        $this->weChat['config']->set('oauth.scopes', ['snsapi_login']);

        $this->weChat['config']->set('oauth.callback', '/oauth/wechat/callback?return_url=' . urlencode($returnUrl));

        $oauth = $this->weChat->oauth;

        return $oauth->redirect();
    }

    public function callback(Request $request)
    {
        $returnUrl = $request->input('return_url');

        $oauth = $this->weChat->oauth;

        try {
            $user = $oauth->user();
        } catch (AuthorizeFailedException $e) {
            $url = $this->buildReturnUrl($returnUrl, ['error' => '授权失败']);

            return redirect()->to($url);
        }

        $token = $this->generateToken($user, 'wechat');

        if (empty($returnUrl)) {
            return response()->json(compact('token'));
        } else {
            $url = $this->buildReturnUrl($returnUrl, $token);

            return redirect()->to($url);
        }
    }
}