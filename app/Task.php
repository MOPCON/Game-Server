<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Task extends Model
{
    protected $table = 'tasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid',
        'vkey_id',
        'name',
        'name_e',
        'description',
        'description_e',
        'image',
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

    public function KeyPool()
    {
        return $this->belongsTo('App\KeyPool', 'vkey_id', 'id');
    }
}
