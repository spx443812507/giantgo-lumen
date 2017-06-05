<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/5
 * Time: 上午11:56
 */

namespace App\Http\Controllers\OAuth;


use Illuminate\Http\Request;
use Overtrue\Socialite\AuthorizeFailedException;
use Overtrue\Socialite\SocialiteManager as Socialite;
use Tymon\JWTAuth\JWTAuth;

class QQController extends AbstractController
{
    protected $qq;

    public function __construct(JWTAuth $jwt)
    {
        parent::__construct($jwt);

        $this->qq = (new Socialite(['qq' => config('qq')]))->driver('qq');
    }

    public function login(Request $request)
    {
        $appId = $request->input('app_id');

        return $this->qq->stateless(false)->redirect();
    }

    public function callback(Request $request)
    {
        $returnUrl = $request->input('return_url');

        try {
            $user = $this->qq->stateless(false)->user();
        } catch (AuthorizeFailedException $e) {
            $url = $this->buildReturnUrl($returnUrl, ['error' => '授权失败']);

            return redirect()->to($url);
        }

        $token = $this->generateToken($user, 'qq');

        if (empty($returnUrl)) {
            return response()->json($token);
        } else {
            $url = $this->buildReturnUrl($returnUrl, $token);

            return redirect()->to($url);
        }
    }
}