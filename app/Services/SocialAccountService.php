<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/8/4
 * Time: 下午3:30
 */

namespace App\Services;

use App\Models\SocialAccount;
use Overtrue\Socialite\User;

class SocialAccountService
{
    public function generateSocialAccount(User $oAuthUser, $provider)
    {
        $socialAccount = SocialAccount::where('provider_id', $oAuthUser->getId())->first();

        if (empty($socialAccount)) {
            $socialAccount = SocialAccount::create([
                'provider_id' => $oAuthUser->getId(),
                'name' => $oAuthUser->getName(),
                'nickname' => $oAuthUser->getNickname(),
                'avatar' => $oAuthUser->getAvatar(),
                'email' => $oAuthUser->getEmail(),
                'provider' => $provider
            ]);
        } else {
            $socialAccount['name'] = $oAuthUser->getName();
            $socialAccount['nickname'] = $oAuthUser->getNickname();
            $socialAccount['avatar'] = $oAuthUser->getAvatar();
            $socialAccount['email'] = $oAuthUser->getEmail();
            $socialAccount['last_auth'] = new \DateTime();
            $socialAccount->save();
        }

        return $socialAccount;
    }
}