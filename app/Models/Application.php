<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/6
 * Time: 上午11:06
 */

namespace App\Models;

class Application extends Model
{
    protected $fillable = [
        'client_id', 'client_secret', 'redirect'
    ];
}