<?php

namespace App\Models;

use App\Traits\Attributable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agenda extends Model
{
    use SoftDeletes, Attributable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'start_date', 'end_date'
    ];

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
    protected $dates = ['start_date', 'end_date'];

    protected $casts = [];

    public function admin()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function speakers()
    {
        return $this->hasMany('App\Models\Speaker');
    }

    public function seminar()
    {
        return $this->belongsTo('App\Models\Seminar');
    }
}
