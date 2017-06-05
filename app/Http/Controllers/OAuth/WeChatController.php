<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/3
 * Time: 下午8:17
 */

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use EasyWeChat\Foundation\Application;
use Illuminate\Http\Request;
use Overtrue\Socialite\AuthorizeFailedException;
use Tymon\JWTAuth\JWTAuth;

class WeChatController extends Controller
{
    protected $jwt;

    protected $weChat;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
        $this->weChat = new Application(config('wechat'));
    }

    private function buildReturnUrl($returnUrl, $params)
    {
        $urlParts = parse_url($returnUrl);

        if (!array_has($urlParts, 'query')) {
            $urlParts['query'] = '';
        }

        parse_str($urlParts['query'], $queries);

        $queries = array_merge($queries, $params);

        $urlParts['query'] = http_build_query($queries);

        $url = http_build_url($urlParts);

        return $url;
    }

    public function weChatLogin(Request $request)
    {
        $appId = $request->input('app_id');

        $returnUrl = $request->input('return_url');

        if (isset($appId)) {
            $this->weChat['config']->set('app_id', $appId);
        }

        $this->weChat['config']->set('oauth.scopes', ['snsapi_login']);

        $this->weChat['config']->set('oauth.callback', '/oauth/wechat/callback?return_url=' . urlencode($returnUrl));

        $oauth = $this->weChat->oauth;

        return $oauth->stateless()->redirect();
    }

    public function weChatCallback(Request $request)
    {
        //是否已经绑定系统用户
        $isBind = false;

        $returnUrl = $request->input('return_url');

        $oauth = $this->weChat->oauth;

        try {
            $user = $oauth->user();
        } catch (AuthorizeFailedException $e) {
            $url = $this->buildReturnUrl($returnUrl, ['error' => '授权失败']);

            return redirect()->to($url);
        }

        $socialAccount = SocialAccount::where('provider_id', $user->getId())->first();

        //如果没有该微信用户
        if (empty($socialAccount)) {
            $socialAccount = SocialAccount::create([
                'provider_id' => $user->getId(),
                'name' => $user->getName(),
                'nickname' => $user->getNickname(),
                'avatar' => $user->getAvatar(),
                'email' => $user->getEmail(),
                'provider' => 'wechat'
            ]);
        } else {
            $socialAccount['last_auth'] = new \DateTime();
            $socialAccount->save();
        }
        //如果已经绑定用户则返回用户token
        if ($socialAccount->user) {
            $user = new User($socialAccount->user->toArray());
            $user['id'] = $socialAccount->user->id;
            $token = $this->jwt->fromUser($user);
            $isBind = true;
        } else {
            $token = $this->jwt->fromUser($socialAccount);
        }

        if (empty($returnUrl)) {
            return response()->json(compact('token'));
        } else {
            $url = $this->buildReturnUrl($returnUrl, [$isBind ? 'token' : 'verify' => $token]);

            return redirect()->to($url);
        }
    }
}