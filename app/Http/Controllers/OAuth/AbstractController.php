<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/5
 * Time: 下午10:01
 */

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Overtrue\Socialite\User;
use Tymon\JWTAuth\JWTAuth;

abstract class AbstractController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    protected function buildReturnUrl($returnUrl, $params)
    {
        $urlParts = parse_url($returnUrl);

        if (!array_has($urlParts, 'query')) {
            $urlParts['query'] = '';
        }

        parse_str($urlParts['query'], $queries);

        $queries = array_merge(array_except($queries, ['token', 'verify']), $params);

        $urlParts['query'] = http_build_query($queries);

        $url = http_build_url($urlParts);

        return $url;
    }

    protected function generateToken(User $user, $provider)
    {
        $hasBind = false;

        $socialAccount = SocialAccount::where('provider_id', $user->getId())->first();

        //如果没有该微信用户
        if (empty($socialAccount)) {
            $socialAccount = SocialAccount::create([
                'provider_id' => $user->getId(),
                'name' => $user->getName(),
                'nickname' => $user->getNickname(),
                'avatar' => $user->getAvatar(),
                'email' => $user->getEmail(),
                'provider' => $provider
            ]);
        } else {
            $socialAccount['last_auth'] = new \DateTime();
            $socialAccount->save();
        }
        //如果已经绑定用户则返回用户token
        if ($socialAccount->user) {
            $user = new \App\Models\User($socialAccount->user->toArray());
            $user['id'] = $socialAccount->user->id;
            $token = $this->jwt->fromUser($user);
            $hasBind = true;
        } else {
            $token = $this->jwt->fromUser($socialAccount);
        }

        return [$hasBind ? 'token' : 'verify' => $token];
    }

    abstract protected function login(Request $request);

    abstract protected function callback(Request $request);
}