<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/5
 * Time: 上午11:56
 */

namespace App\Http\Controllers\OAuth;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Overtrue\Socialite\SocialiteManager as Socialite;
use Tymon\JWTAuth\JWTAuth;

class QQController extends Controller
{
    protected $jwt;

    protected $qq;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
        $this->qq = (new Socialite(['qq' => config('qq')]))->driver('qq');
    }

    public function qqLogin(Request $request, $appId)
    {
//        $this->qq->setConfig();
        return $this->qq->stateless(false)->redirect();
    }

    public function qqCallback(Request $request, $appId)
    {
        $this->qq->setConfig();
        $this->qq->stateless(false)->redirect();
    }
}