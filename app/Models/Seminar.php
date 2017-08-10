<?php

namespace App\Models;

use App\Traits\Attributable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seminar extends Model
{
    use SoftDeletes, Attributable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'start_at', 'end_at', 'closed_at'
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
    protected $dates = ['start_at', 'end_at', 'closed_at'];

    protected $casts = [
        'need_audit' => 'boolean'
    ];

    public function setStartAtAttribute($value)
    {
        $this->attributes['start_at'] = Carbon::createFromFormat(\DateTime::ATOM, $value);
    }

    public function setEndAtAttribute($value)
    {
        $this->attributes['end_at'] = Carbon::createFromFormat(\DateTime::ATOM, $value);
    }

    public function setClosedAtAttribute($value)
    {
        $this->attributes['closed_at'] = Carbon::createFromFormat(\DateTime::ATOM, $value);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function contacts()
    {
        return $this->belongsToMany('App\Models\Contact');
    }

    public function agendas()
    {
        return $this->hasMany('App\Models\Agenda');
    }
}
