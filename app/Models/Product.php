<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/5/21
 * Time: 上午9:36
 */

namespace App\Models;


class Product extends Model
{
    public $fillable = ['title', 'description', 'price'];


    public function users()
    {
        return $this->belongsTo('App\Models\User');
    }
}