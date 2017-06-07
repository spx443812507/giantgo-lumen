<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/7
 * Time: 上午11:12
 */

namespace App\Http\Controllers;

use EasyWeChat\Core\Http;
use EasyWeChat\Foundation\Application;
use EasyWeChat\OpenPlatform\Guard;
use Illuminate\Http\Request;
use Overtrue\LaravelWechat\CacheBridge;
use Tymon\JWTAuth\JWTAuth;

class PlatformController extends Controller
{
    protected $jwt;

    protected $weChat;

    protected $config;

    protected $scope;

    protected $redirectUri;

    protected $componentId;

    protected $cache;

    public function __construct(JWTAuth $jwt, Application $app)
    {
        $this->jwt = $jwt;
        $this->weChat = $app;
        $this->config = config('wechat');
        $this->scope = env('WECHAT_OAUTH_SCOPES');
        $this->redirectUri = env('WEIXIN_OPEN_PLATFORM_REDIRECT_URI');
        $this->componentId = env('WEIXIN_OPEN_PLATFORM_KEY');
        $this->cache = new CacheBridge();
    }

    public function login(Request $request)
    {

        $appId = $request->input('app_id');

        $scope = $request->input('scope');

        $authUrl = [
            'appid' => $appId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => $scope ?: $this->scope,
            'component_appid' => $this->componentId
        ];

        $query = http_build_query($authUrl, '', '&', PHP_QUERY_RFC1738);

        return redirect('https://open.weixin.qq.com/connect/oauth2/authorize' . '?' . $query . '#wechat_redirect');
    }

    public function callback(Request $request)
    {
        $code = $request->input('code');

        $appId = $request->input('appid');

        $http = new Http();

        $result = $http->get('http://devswcb.smarket.net.cn/', ['code' => $code, 'appId' => $appId]);

        return response()->json($result, 200);
    }

    /**
     * 微信公众号授权第三方平台
     */
    public function auth()
    {
        $openPlatform = $this->weChat->open_platform;

        $oauth = $openPlatform->oauth;

        $response = $oauth->scopes(['snsapi_userinfo'])->redirect();

        // 直接跳转
//        $response = $openPlatform->pre_auth->redirect('http://password.smarket.net.cn/oauth/wechat/open/response');
        // 获取跳转的 URL
        return "";
    }

    /**
     * 微信公众号授权第三方平台回调
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response()
    {
        $openPlatform = $this->weChat->open_platform;

        $server = $openPlatform->server;

        $server->setMessageHandler(function ($event) use ($openPlatform) {
            // 事件类型常量定义在 \EasyWeChat\OpenPlatform\Guard 类里
            switch ($event->InfoType) {
                case Guard::EVENT_AUTHORIZED: // 授权成功
                    $authorizationInfo = $openPlatform->getAuthorizationInfo($event->AuthorizationCode);

                    $app = $openPlatform->createAuthorizerApplication($authorizationInfo['authorizer_appid'], $authorizationInfo['authorizer_refresh_token']);

                    $authorizer = $app->open_platform->authorizer;
                    $refreshTokenKey = $authorizer->getRefreshTokenCacheKey();

                    $this->cache->fetch($refreshTokenKey);
                //todo 从配置文件读取token key，并且提供根据appId创建app方法
                // 保存数据库操作等...
                case Guard::EVENT_UPDATE_AUTHORIZED: // 更新授权
                    // 更新数据库操作等...
                case Guard::EVENT_UNAUTHORIZED: // 授权取消
                    // 更新数据库操作等...
            }
        });

        $response = $server->serve();

        return $response;
    }
}