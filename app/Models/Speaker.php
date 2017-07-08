<?php

namespace App\Models;

use App\Models\EAV\Entity as EavEntity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Speaker extends EavEntity
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    protected $casts = [];

    public function admin()
    {
        return $this->belongsTo('App\Models\User');
    }
}
