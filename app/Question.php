<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Question extends Model
{
    protected $table = 'questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid',
        'task_id',
        'vkey_id',
        'name',
        'name_e',
        'description',
        'description_e',
    ];

    protected $hidden = [
        'id',
        'vkey_id',
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

    public function KeyPool()
    {
        return $this->belongsTo('App\KeyPool', 'vkey_id', 'id');
    }

    public function task()
    {
        return $this->belongsTo('App\Task', 'task_id', 'id');
    }

    public function scores()
    {
        return $this->hasMany('App\Scoreboard', 'id', 'question_id');
    }

}
