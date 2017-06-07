<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/5
 * Time: 下午12:02
 */

return [
    'qq' => [
        'client_id' => env('QQ_KEY'),
        'client_secret' => env('QQ_SECRET'),
        'redirect' => env('QQ_REDIRECT_URI'),
    ],
    'wechat' => [
        'client_id' => env('WEIXIN_KEY'),
        'client_secret' => env('WEIXIN_SECRET'),
        'redirect' => env('WEIXIN_REDIRECT_URI'),
    ],
    'wechat_web' => [
        'client_id' => env('WEIXIN_WEB_KEY'),
        'client_secret' => env('WEIXIN_WEB_SECRET'),
        'redirect' => env('WEIXIN_WEB_REDIRECT_URI'),
    ],
    'wechat_open' => [
        'client_id' => env('WEIXIN_OPEN_PLATFORM_KEY'),
        'client_secret' => env('WEIXIN_OPEN_PLATFORM_SECRET'),
        'redirect' => env('WEIXIN_OPEN_PLATFORM_REDIRECT_URI'),
    ]
];