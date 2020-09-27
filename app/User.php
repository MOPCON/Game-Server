<?php

namespace App;

use App\Task;
use App\Mission;
use App\Reward;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable;

    const COMPLETED_TASK = 'completed_task';
    const WON_REWARD = 'won_reward';
    const WON_POINT = 'won_point';

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid', 'email',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'password',
        'email',
        'achievement',
        'favorite',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'mission_list',
        'reward_list',
        self::WON_POINT,
    ];

    protected $casts = [
        'achievement' => 'array',
        'favorite' => 'array',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->achievement = [
                self::COMPLETED_TASK => [],
                self::WON_REWARD => [],
                self::WON_POINT => 0,
            ];
        });
    }

    /**
     * JWT
     *
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWT
     *
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getMissionListAttribute()
    {
        $missions = Mission::with('task')->get();
        return $missions;
    }

    public function getRewardListAttribute()
    {
        $rewards = Reward::all();
        $won_reward = collect($this->achievement[self::WON_REWARD])
            ->mapWithKeys(function ($item) {
                return [$item['reward_id'] => $item['redeemed']];
            })->all();

        return $rewards->map(function ($item) use ($won_reward) {
            if (array_key_exists($item->id, $won_reward)) {
                $item->redeemed = (int) $won_reward[$item->id];
                $item->has_won = 1;
            } else {
                $item->redeemed = 0;
                $item->has_won = 0;
            }

            return $item;
        });
    }

    public function getWonPointAttribute()
    {
        return $this->achievement[self::WON_POINT];
    }

    /**
     * get scores for user
     */
    public function scores()
    {
        return $this->hasMany('App\Scoreboard');
    }
}
