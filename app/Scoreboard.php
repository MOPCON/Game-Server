<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scoreboard extends Model
{
    protected $table = 'scoreboard';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'mission_id',
        'task_id',
        'pass',
        'point',
    ];

    public function mission()
    {
        return $this->belongsTo('App\Mission');
    }

    public function task()
    {
        return $this->belongsTo('App\Task');
    }
}
