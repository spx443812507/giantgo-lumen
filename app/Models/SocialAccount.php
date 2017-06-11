<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/4
 * Time: 上午11:13
 */

namespace App\Models;


use Tymon\JWTAuth\Contracts\JWTSubject;

class SocialAccount extends Model implements JWTSubject
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider_id', 'name', 'nickname', 'email', 'avatar', 'provider'
    ];

    protected $table = 'social_accounts';

    protected $dates = ['last_auth'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return ['provider' => $this->attributes['provider']];
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer', 'entity_id');
    }
}