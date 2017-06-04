<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/3
 * Time: 下午8:17
 */

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\User;
use EasyWeChat\Foundation\Application;
use Illuminate\Http\Request;
use Overtrue\Socialite\AuthorizeFailedException;
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
        $state = $request->input('state');

        $this->weChat['config']->set('oauth.scopes', ['snsapi_login']);

        $this->weChat['config']->set('oauth.callback', '/api/oauth/applications/' . $appId . '/wechat/callback?state=' . $state);

        $oauth = $this->weChat->oauth;

        return $oauth->stateless()->redirect();
    }

    public function weChatCallback(Request $request)
    {
        //是否已经绑定系统用户
        $isBind = false;

        $returnUrl = $request->input('state');

        $oauth = $this->weChat->oauth;

        try {
            $user = $oauth->user();
        } catch (AuthorizeFailedException $e) {

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
            $urlParts = parse_url($returnUrl);

            parse_str($urlParts['query'], $queries);

            $queries[$isBind ? 'token' : 'verify'] = $token;

            $urlParts['query'] = http_build_query($queries);

            return redirect()->to($urlParts['scheme'] . '://' . $urlParts['host'] . (empty($urlParts['port']) ? '' : ':' . $urlParts['port']) . $urlParts['path'] . '?' . $urlParts['query']);
        }
    }
}