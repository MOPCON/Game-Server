<?php

namespace App\Http\Controllers;

use App\Mission;
use App\Task;
use App\Scoreboard;
use App\Http\Traits\ApiTrait;
use App\Http\Traits\AuthTrait;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ApiTrait;
    use AuthTrait;

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTask()
    {
        $user = $this->guard()->user();
        $scoreBoard = Scoreboard::where('user_id', $user->id)
            ->with(array('mission' => function ($query) {
                $query->where('open', 1);
            }))->get();
        if ($scoreBoard->isEmpty()) {
            $insertTask = [];
            Mission::where('open', 1)
                ->with('task')->orderby('order')
                ->each(function ($mission) use ($user, &$insertTask) {
                    $insertTask[] = [
                        'user_id' => $user->id,
                        'mission_id' => $mission->id,
                        'task_id' => $mission->task->id,
                    ];
                });
            Scoreboard::insert($insertTask);
            $scoreBoard->fresh();
        }
        $missions = [];
        $scoreBoard->each(function ($scoreData) use (&$missions) {
            $missions[] = [
                'uid' => $scoreData->mission->uid,
                'name' => $scoreData->mission->name,
                'name_e' => $scoreData->mission->name_e,
                'description' => $scoreData->mission->description,
                'description_e' => $scoreData->mission->description_e,
                'order' => $scoreData->mission->order,
                'passed' => $scoreData->pass === 1,
            ];
        });

        $passCount = $scoreBoard->sum('pass');
        $taskCount = $scoreBoard->count();

        $output = [
            'missions' => $missions,
            'passed' => $passCount,
            'total' => $taskCount,
        ];


        return $this->returnSuccess('Success.', $output);
        }

        Scoreboard::create([
            'user_id' => $user->id,
            'mission_id' => $mission->id,
            'task_id' => $task->id,
        ]);

        return $this->returnSuccess('Success.', $task);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $task = Task::create($request->only([
            'vkey_id',
            'name',
            'name_e',
            'description',
            'description_e',
            'image',
        ]));

        return $this->returnSuccess('Store success.', $task);
    }
}
