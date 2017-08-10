<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Checkin extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seminar_id', 'title', 'staff_name', 'staff_mobile'
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
}
