<?php

namespace App\Models;

use App\Traits\Attributable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Speaker extends Model
{
    use SoftDeletes, Attributable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'mobile', 'email', 'company', 'position', 'profile'];

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

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function seminar()
    {
        return $this->belongsTo('App\Models\Seminar');
    }

    public function agendas()
    {
        return $this->belongsToMany('App\Models\Agenda');
    }

}
