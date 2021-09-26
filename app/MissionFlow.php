<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MissionFlow extends Model
{

    protected $table = 'mission_flow';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mission_id',
        'task_id',
        'next_task_id',
        'disabled'
    ];

    public function mission()
    {
        return $this->belongsTo('App\Mission');
    }

    public function task()
    {
        return $this->belongsTo('App\Task', 'task_id', 'id');
    }

    public function nextMission()
    {
        return $this->belongsTo('App\Mission', 'next_mission_id', 'id');
    }
}
