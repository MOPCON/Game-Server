<?php

namespace App\Http\Controllers;

use App\Mission;
use App\Task;
use App\Scoreboard;
use App\Http\Traits\ApiTrait;
use App\Http\Traits\AuthTrait;
use App\User;
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
        $scores = new Scoreboard();
        $scoreBoard = $scores->getUserScores($user);

        if ($scoreBoard->isEmpty()) {
            $scores->generateScores($user);
            $scoreBoard = $scores->getUserScores($user);
        }
        // TODO: 過關的判斷要調整
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

        $rewardInfo = [
            'count' => intval($passCount / 6),
            'exchanged' => collect($user->achievement[User::WON_REWARD])->count(),
        ];

        $output = [
            'missions' => $missions,
            'passed' => $passCount,
            'total' => $taskCount,
            'rewardInfo' => $rewardInfo,
        ];


        return $this->returnSuccess('Success.', $output);
    }

    /**
     * @param string $missionUid
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTaskByMission(string $missionUid)
    {
        $tasks = Task::where('mission_uid', $missionUid)->get();

        if ($tasks->isEmpty()) {
            return $this->return404Response();
        }

        return $this->returnSuccess('Success.', $tasks);
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
            'name',
            'name_e',
            'description',
            'description_e',
            'image',
        ]));

        return $this->returnSuccess('Store success.', $task);
    }
}
