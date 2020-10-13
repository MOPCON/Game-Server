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
        if ($this->scores->isEmpty()) {
            $scores = new Scoreboard();
            $scores->generateScores($this);
            $this->refresh();
        }

        $scoresBoard = $this->scores->mapWithKeys(function ($score) {
            return [$score['mission_id'] => $score['pass']];
        });
        return Mission::with('task')->get()
            ->map(function ($mission) use ($scoresBoard) {
                $mission->pass = $scoresBoard[$mission['id']];
                return $mission;
            });
    }

    public function getRewardListAttribute()
    {
        $rewards = Reward::all();
        return collect($this->achievement[self::WON_REWARD])
            ->map(function ($item) use ($rewards) {
                $reward_item = $rewards[$item['reward_id']];
                $reward_item->redeemed = (int) $item['redeemed'];
                $reward_item->has_won = 1;

                return $reward_item;
            })->all();
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
