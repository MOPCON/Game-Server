<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Mission extends Model
{
    protected $table = 'missions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid',
        'name',
        'name_e',
        'description',
        'description_e',
        'open',
        'point',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uid = Str::uuid();
        });
    }

    public function task()
    {
        return $this->hasMany('App\Task', 'mission_uid', 'uid');
    }

    public function scores()
    {
        return $this->hasMany('App\Scoreboard', 'id', 'mission');
    }

    public function flow()
    {
        return $this->hasMany('App\MissionFlow', 'id', 'mission_id');
    }
}
