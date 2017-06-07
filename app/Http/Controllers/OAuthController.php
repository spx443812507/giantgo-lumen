<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/6
 * Time: 上午11:42
 */

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Overtrue\Socialite\AuthorizeFailedException;
use Overtrue\Socialite\Config;
use Overtrue\Socialite\User;
use Tymon\JWTAuth\JWTAuth;
use Overtrue\Socialite\SocialiteManager as Socialite;

class OAuthController extends Controller
{
    protected $jwt;

    protected $config;

    protected $socialite;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;

        $this->config = config('oauth');

        $this->socialite = new Socialite($this->config);
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

    protected function buildConfig($appId, $returnUrl)
    {
        $application = Application::where('client_id', $appId)->first();

        if (isset($application)) {
            $this->config = new Config([$application['provider'] => [
                'client_id' => $application['client_id'],
                'client_secret' => $application['client_secret'],
                'redirect' => $application['redirect'] . '?app_id=' . $appId . '&return_url=' . urlencode($returnUrl)
            ]]);
        }

        return $this->config;
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
            $socialAccount['name'] = $user->getName();
            $socialAccount['nickname'] = $user->getNickname();
            $socialAccount['avatar'] = $user->getAvatar();
            $socialAccount['email'] = $user->getEmail();
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

    public function login(Request $request, $provider)
    {
        $appId = $request->input('app_id');

        $scope = $request->input('scope');

        if (isset($appId)) {
            $config = $this->buildConfig($appId, $request->input('return_url'));

            if (isset($config)) {
                $this->socialite->config($config);
            }
        }

        $this->socialite = $this->socialite->with($provider);

        if (!empty($scope)) {
            $this->socialite->scopes([$scope]);
        }

        return $this->socialite->stateless(false)->redirect();
    }

    public function callback(Request $request, $provider)
    {
        $returnUrl = $request->input('return_url');

        $appId = $request->input('app_id');

        if (isset($appId)) {
            $config = $this->buildConfig($appId, $request->input('return_url'));

            if (isset($config)) {
                $this->socialite->config($config);
            }
        }

        try {
            $user = $this->socialite->with($provider)->stateless(false)->user();
        } catch (AuthorizeFailedException $e) {
            $url = $this->buildReturnUrl($returnUrl, ['error' => '授权失败']);

            return redirect()->to($url);
        }

        $token = $this->generateToken($user, $provider);

        if (empty($returnUrl)) {
            return response()->json($token);
        } else {
            $url = $this->buildReturnUrl($returnUrl, $token);

            return redirect()->to($url);
        }
    }
}