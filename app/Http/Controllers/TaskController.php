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
        $missions = [];
        $scoreBoard->each(function ($scoreData) use (&$missions) {
            $missions[] = [
                'uid' => $scoreData->mission->uid,
                'name' => $scoreData->mission->name,
                'name_e' => $scoreData->mission->name_e,
                'description' => explode("<!--more-->", $scoreData->mission->description),
                'description_e' => $scoreData->mission->description_e,
                'image' => $scoreData->mission->image == null ? [] : explode(",", $scoreData->mission->image),
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
        $gift_mission_uid = env('GIFT_MISSION_UID', '');
        $tasks = [];
        $with_question = true;
        if ($missionUid == $gift_mission_uid) {
            // check user
            $user = $this->guard()->user();
            $count = collect($user->achievement[User::WON_REWARD])->count();
            $current_mission = Mission::where('id', $user->getCurrentMissionAttribute())->first();
            if ($count > 0 || ($current_mission != null && $current_mission->uid == $gift_mission_uid)) {
                $with_question = true;
            } else {
                $with_question = false;
            }
        }
        if ($with_question) {
            $tasks = Task::where('mission_uid', $missionUid)
            ->with('questions')
            ->get();
        } else {
            $tasks = Task::where('mission_uid', $missionUid)
            ->get();
        }



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
