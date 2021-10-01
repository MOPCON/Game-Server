<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KeyPool extends Model
{
    const TYPE_QUESTION = 'question';
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

    public function questions()
    {
        return $this->hasMany('App\Question', 'vkey_id', 'id');
    }
}
