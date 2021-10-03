<?php

namespace App;

use Illuminate\Support\Facades\DB;
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

    /**
     * @param User $user
     * @return void
     */
    public function generateScores(User $user): void
    {
        $insertTask = [];
        Mission::where('open', 1)
            ->with('task')->orderby('order')
            ->each(function ($mission) use ($user, &$insertTask) {
                foreach ($mission->task as $task) {
                    foreach ($task->questions as $question) {
                        $insertTask[] = [
                            'user_id' => $user->id,
                            'mission_id' => $mission->id,
                            'task_id' => $task->id,
                            'question_id' => $question->id,
                        ];
                    }
                }
            });
        self::insert($insertTask);
    }

    /**
     * @param User $user
     * @return \Illuminate\Support\Collection
     */
    public function getUserScores(User $user)
    {
        return self::where(function ($query) use ($user) {
            $query = self::where('user_id', $user->id)
                ->with(array('mission' => function ($mission_query) {
                    $mission_query->where('open', 1);
                }))
                ->groupBy(['mission_id', 'task_id'])
                ->select('mission_id', 'task_id', DB::raw('min(pass) as pass'));
            })
            ->groupBy('mission_id')
            ->select('mission_id', DB::raw('min(pass) as pass'))
            ->with('mission')
            ->get();
    }

    public function mission()
    {
        return $this->belongsTo('App\Mission');
    }

    public function task()
    {
        return $this->belongsTo('App\Task');
    }
}
