<?php

namespace App\Models;

use App\Traits\Attributable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class Contact extends Model implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, SoftDeletes, EntrustUserTrait, Attributable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'mobile', 'name', 'password', 'is_active'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'deleted_at'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['last_login'];

    protected $casts = [
        'verified_email' => 'boolean',
        'verified_mobile' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'provider' => 'giantgo',
            'roles' => ['contact']
        ];
    }

    public function socialAccounts()
    {
        return $this->hasMany('App\Models\SocialAccount');
    }
}