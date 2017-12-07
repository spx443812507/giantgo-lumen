<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/6
 * Time: 上午11:42
 */

namespace App\Http\Controllers;

use App\Models\Application;
use App\Services\ContactService;
use App\Services\SocialAccountService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Overtrue\Socialite\AuthorizeFailedException;
use Overtrue\Socialite\Config;
use Tymon\JWTAuth\JWTAuth;
use Overtrue\Socialite\SocialiteManager as Socialite;

class OAuthController extends Controller
{
    protected $jwt;

    protected $config;

    protected $contactService;

    protected $socialAccountService;

    protected $socialite;

    public function __construct(JWTAuth $jwt, ContactService $contactService, SocialAccountService $socialAccountService)
    {
        $this->jwt = $jwt;

        $this->config = config('oauth');

        $this->contactService = $contactService;

        $this->socialAccountService = $socialAccountService;

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

        return http_build_url($urlParts);
    }

    protected function buildConfig($appId, $returnUrl)
    {
        $application = Application::where('client_id', $appId)->first();

        if (isset($application)) {
            $this->config = new Config([$application['provider'] => [
                'client_id' => $application['client_id'],
                'client_secret' => $application['client_secret'],
                'redirect' => $this->buildReturnUrl($application['redirect'], ['app_id' => $appId, 'return_url' => $returnUrl])
            ]]);
        } else {
            throw new Exception('application_not_exists');
        }

        return $this->config;
    }

    public function login(Request $request, $provider)
    {
        //第三方应用Id
        $appId = $request->input('app_id');
        //授权作用域
        $scope = $request->input('scope');
        //已登录用户token
        $token = $request->input('token');
        //授权成功后的返回地址
        $returnUrl = $request->input('return_url');

        if (isset($appId)) {
            $config = $this->buildConfig($appId, $returnUrl);

            if (isset($config)) {
                $this->socialite->config($config);
            }
        }

        $this->socialite = $this->socialite->with($provider);

        if (!empty($scope)) {
            $this->socialite->scopes([$scope]);
        }

        return $this->socialite->stateless()->redirect(null, $token);
    }

    public function callback(Request $request, $provider)
    {
        $returnUrl = $request->input('return_url');

        $appId = $request->input('app_id');

        $token = $request->input('state');

        if (isset($appId) && !empty($appId)) {
            $config = $this->buildConfig($appId, $returnUrl);

            if (isset($config)) {
                $this->socialite->config($config);
            }
        }

        try {
            $oAuthUser = $this->socialite->with($provider)->stateless()->user();

            $socialAccount = $this->socialAccountService->generateSocialAccount($oAuthUser, $provider);

            if (isset($token) && !empty($token)) {
                $contactInfo = Auth::guard('api')->setToken($token);

                $contact = $contactInfo->user();

                if (!empty($socialAccount->contact) && $socialAccount->contact->id !== $contact->id) {
                    return redirect()->to($this->buildReturnUrl($returnUrl, ['error' => '该第三方用户已绑定其它账号']));
                }

                $this->contactService->bindSocialAccount($contact, $socialAccount);

                return redirect()->to($returnUrl);
            } else {
                $params = [];

                if (!empty($socialAccount->contact)) {
                    $params['token'] = $this->jwt->fromUser($socialAccount->contact);
                } else {
                    $params['verify'] = $this->jwt->fromUser($socialAccount);
                }

                return redirect()->to($this->buildReturnUrl($returnUrl, $params));
            }
        } catch (AuthorizeFailedException $e) {
            return redirect()->to($this->buildReturnUrl($returnUrl, ['error' => '授权失败，请重试']));
        } catch (Exception $e) {
            throw $e;
        }
    }
}