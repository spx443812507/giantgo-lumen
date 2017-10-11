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
        'title', 'start_at', 'end_at', 'closed_at', 'need_audit'
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
        if (strlen($value)) {
            $this->attributes['start_at'] = Carbon::createFromFormat(\DateTime::ATOM, $value);
        } else {
            $this->attributes['start_at'] = null;
        }
    }

    public function setEndAtAttribute($value)
    {
        if (strlen($value)) {
            $this->attributes['end_at'] = Carbon::createFromFormat(\DateTime::ATOM, $value);
        } else {
            $this->attributes['end_at'] = null;
        }
    }

    public function setClosedAtAttribute($value)
    {
        if (strlen($value)) {
            $this->attributes['closed_at'] = Carbon::createFromFormat(\DateTime::ATOM, $value);
        } else {
            $this->attributes['closed_at'] = null;
        }
    }

    public function entity()
    {
        return $this->belongsTo('App\Models\EAV\Entity', 'entity_type_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function contacts()
    {
        return $this->belongsToMany('App\Models\Contact', 'seminar_contact');
    }

    public function agendas()
    {
        return $this->hasMany('App\Models\Agenda');
    }

    public function checkins()
    {
        return $this->hasMany('App\Models\Checkin');
    }

    public function speakers()
    {
        return $this->hasMany('App\Models\Speaker');
    }
}
