<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/7
 * Time: 上午11:12
 */

namespace App\Http\Controllers;

use EasyWeChat\Foundation\Application;
use EasyWeChat\OpenPlatform\Guard;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Overtrue\LaravelWechat\CacheBridge;
use Overtrue\Socialite\User;
use Tymon\JWTAuth\JWTAuth;

class PlatformController extends OAuthController
{
    protected $weChat;

    protected $config;

    protected $scope;

    protected $redirectUri;

    protected $componentId;

    protected $cache;

    public function __construct(JWTAuth $jwt, Application $app)
    {
        parent::__construct($jwt);

        $this->weChat = $app;
        $this->config = config('wechat');
        $this->scope = env('WECHAT_OAUTH_SCOPES');
        $this->redirectUri = env('WEIXIN_OPEN_PLATFORM_REDIRECT_URI');
        $this->componentId = env('WEIXIN_OPEN_PLATFORM_KEY');
        $this->cache = new CacheBridge();
    }

    public function login(Request $request, $provider = 'wechat_open')
    {
        $appId = $request->input('app_id');

        $scope = $request->input('scope');

        $returnUrl = $request->input('return_url');

        $authUrl = [
            'appid' => $appId,
            'redirect_uri' => $this->buildReturnUrl($this->redirectUri, ['return_url' => $returnUrl]),
            'response_type' => 'code',
            'scope' => $scope ?: $this->scope,
            'component_appid' => $this->componentId
        ];

        $query = http_build_query($authUrl, '', '&', PHP_QUERY_RFC1738);

        return redirect('https://open.weixin.qq.com/connect/oauth2/authorize' . '?' . $query . '#wechat_redirect');
    }

    public function callback(Request $request, $provider = 'wechat_open')
    {
        $code = $request->input('code');

        $appId = $request->input('appid');

        $returnUrl = $request->input('return_url');

        $http = new Client();

        $result = $http->request('post', 'http://devswcb.wechat.smarket.net.cn/index.php', ['json' =>
            ["command" =>
                ["size" => 0,
                    "orn" => "02-0001-00000001",
                    "dst" => "01-0401-00000001",
                    "type" => "0x0002",
                    "cmd" => 'contact.getInfoByCode',
                    "sess" => '',
                    "seq" => '0',
                    "ver" => '1000',
                    "body" => ['code' => $code, 'appId' => $appId]
                ]
            ]
        ]);

        $content = json_decode($result->getBody()->getContents());

        if ($content->body->result == 0) {
            $user = $content->body->content;

            $user = new User([
                'id' => $user->openid,
                'name' => $user->nickname,
                'nickname' => $user->nickname,
                'avatar' => $user->headimgurl,
                'email' => null,
                'original' => $user->privilege,
            ]);
        } else {
            $url = $this->buildReturnUrl($returnUrl, ['error' => '授权失败，请重试']);

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

    /**
     * 微信公众号授权第三方平台
     */
    public function auth()
    {
        $openPlatform = $this->weChat->open_platform;
        // 直接跳转
        $response = $openPlatform->pre_auth->redirect('http://password.smarket.net.cn/oauth/wechat/open/response');
        // 获取跳转的 URL
        return $response->getTargetUrl();
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