<?php

namespace App\Models;

use App\Traits\Attributable;
use Carbon\Carbon;
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
        'title', 'start_at', 'end_at'
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
    protected $dates = ['start_at', 'end_at'];

    protected $casts = [];

    public function setStartAtAttribute($value)
    {
        $this->attributes['start_at'] = Carbon::createFromFormat(\DateTime::ATOM, $value);
    }

    public function setEndAtAttribute($value)
    {
        $this->attributes['end_at'] = Carbon::createFromFormat(\DateTime::ATOM, $value);
    }

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
