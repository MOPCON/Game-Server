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
     * @param string $missionUid
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTask(string $missionUid)
    {
        $user = $this->guard()->user();

        $mission = Mission::where([['uid', $missionUid], ['open', 1]])->firstOrFail();

        $scores = $user->scores()->get();

        $existTask = $scores->where('mission_id', $mission->id);
        if (! $existTask->isEmpty()) {
            return $this->returnSuccess(
                'Success.',
                Task::find($existTask->first()->task_id)
            );
        }

        $attendTaskIds = $scores->map(function ($item) {
            return $item->task_id;
        })->all();

        $task = Task::whereNotIn('id', $attendTaskIds)
            ->inRandomOrder()
            ->first();

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
