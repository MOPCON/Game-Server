<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KeyPool extends Model
{
    const TYPE_TASK = 'task';
    const TYPE_REWARD = 'reward';
    protected $table = 'key_pool';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'type',
        'note',
        'slug',
        'account',
        'passwd',
    ];

    public function tasks()
    {
        return $this->hasMany('App\Task', 'vkey_id', 'id');
    }
}
