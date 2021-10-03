<?php

namespace App\Http\Controllers;

use App\User;
use App\Task;
use App\Reward;
use App\KeyPool;
use App\Question;
use App\MissionFlow;
use App\Http\Traits\ApiTrait;
use App\Http\Traits\AuthTrait;
use Illuminate\Http\Request;

class VerifyController extends Controller
{
    use ApiTrait;
    use AuthTrait;

    /**
     * @param Request $request
     * @param string $vType
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request, string $vType)
    {
        if (! $request->filled(['answer']) || ! is_array($request->input('answer'))) {
            return $this->return400Response($this->returnWrongAnswerMessage());
        }

        $user = $this->guard()->user();
        $achievement = $user->achievement;

        $answers = $request->input('answer');

        $valid_result = [];

        foreach($answers as $answer) {
            $uid = $answer['uid'];
            $vkey = $answer['vkey'];

            if (! $this->checkKey($uid, $vkey, $vType)) {
                array_push($valid_result, [
                    'uid' => $uid,
                    'pass' => false,
                    'message' => $this->returnWrongAnswerMessage()
                ]);
                continue;
            }

            switch ($vType) {
                case KeyPool::TYPE_QUESTION:
                    array_push($valid_result, $this->handleQuestionProcess($uid, $user, $achievement));
                    break;
                case KeyPool::TYPE_REWARD:
                    $reward = Reward::where('uid', $uid)->firstOrFail();
                    if (! $reward->redeemable) {
                        array_push($valid_result, [
                            'uid' => $uid,
                            'pass' => false,
                            'message' => "獎品已兌換完畢囉。"
                        ]);
                        break;
                    }

                    $reward_id = $reward->id;
                    $rewardCollection = collect($achievement[User::WON_REWARD]);
                    $newCollection = null;

                    $exchage_reward = $rewardCollection->where('reward_id', $reward_id)
                        ->firstWhere('redeemed', false);

                    if (!empty($exchage_reward)) {
                        $exchanged = false;
                        $newCollection = $rewardCollection->map(
                            function ($item) use ($reward_id, &$exchanged) {
                                if ($exchanged) {
                                    return $item;
                                }

                                if ($item['reward_id'] !== $reward_id) {
                                    return $item;
                                }

                                if ($item['redeemed'] === true) {
                                    return $item;
                                }

                                $exchanged = true;
                                $item['redeemed'] = true;

                                return $item;
                            }
                        );
                    } else {
                        array_push($valid_result, [
                            'uid' => $uid,
                            'pass' => false,
                            'message' => "驗證碼輸入錯誤，請檢查後重新輸入。"
                        ]);
                    }

                    if ($newCollection) {
                        $achievement[User::WON_REWARD] = $newCollection->all();
                    }

                    $this->saveAchievement($user, $achievement);

                    array_push($valid_result, [
                        'uid' => $uid,
                        'pass' => true,
                        'success' => null
                    ]);

                    break;
            }


        }

        return $this->returnSuccess($valid_result);
    }


    /**
     * @param string $uid
     * @param string $key
     * @param string $type
     * @return boolean
     */
    private function checkKey(string $uid, string $key, string $type)
    {
        $result = false;

        switch ($type) {
            case KeyPool::TYPE_QUESTION:
                $result = $this->checkQuestionKey($uid, $key);
                break;
            case KeyPool::TYPE_REWARD:
                $result = KeyPool::where([
                    ['key', $key],
                    ['type', $type]
                ])->exists();
                break;
        }

        return $result;
    }


    /**
     * @param string $uid
     * @param string $key
     * @return boolean
     */
    private function checkQuestionKey(string $uid, string $key): bool {

        $result = false;

        $input_type = env('ANSWER_INPUT_TYPE', 'qrcode');

        $check_timestamp = ($input_type == 'qrcode');

        $question = Question::where('uid', $uid)->firstOrFail();
        $vkey = $question->KeyPool->key;

        if ($check_timestamp) {
            if (strpos($key, '+') !== false) {
                $tmp = explode('+', $key);
                if ($tmp[1] > strtotime('-60 seconds')) {
                    $result = md5($vkey . '+' . $tmp[1]) == $tmp[0];
                }
            }
        } else {
            $result = ($key == $vkey);
        }
        return $result;
    }

    /**
     * @return string
     */
    private function returnWrongAnswerMessage(): string {

        $input_type = env('ANSWER_INPUT_TYPE', 'qrcode');

        if ($input_type == 'qrcode') {
            return '非本關卡正確 QRcode，請重新確認。';
        } else {
            return '請檢查您的答案';
        }

    }

    /**
     * @param string $uid
     * @param User $user
     * @param array $achievement
     */
    private function handleQuestionProcess(string $uid, User $user, &$achievement) {

        $enable_flow = env('ENABLE_FLOW_CTRL', false);

        $question = Question::where('uid', $uid)->firstOrFail();

        $task = $question->task;
        $task_id = $task->id;
        $taskCollection = collect($achievement[User::COMPLETED_TASK]);

        if (!$taskCollection->where('task_id', $task_id)->where('question_id', $question->id)->isEmpty()) {
            // 已經過關 bypass
            return [
                'uid' => $uid,
                'pass' => true,
                'message' => null
            ];
        }

        if ($enable_flow && ! $this->isLegalPathToAnswer($user->getCurrentMissionAttribute(), $task)) {
            return [
                'uid' => $uid,
                'pass' => false,
                'message' => '前置任務尚未完成，請先完成後再來挑戰本關。'
            ];
        }

        $score = $user
            ->scores()
            ->get()
            ->firstWhere('question_id', $question->id);
        if ($score) {
            if ($score->pass == 0) {
                $score->pass = 1;
                $score->save();

                array_push(
                    $achievement[User::COMPLETED_TASK],
                    [
                        'mission_id' => $score->mission_id,
                        'task_id' => $score->task_id,
                        'question_id' => $score->question_id
                    ]
                );

                $task_not_pass = $user
                    ->scores()
                    ->get()
                    ->where('task_id', $task_id)
                    ->where('pass', false);

                if ($task_not_pass->isEmpty()) {
                    // 全數通關後再往後移動
                    $flow = MissionFlow::where('mission_id', $score->mission_id)
                        ->where('task_id', $score->task_id)
                        ->first();
                    $achievement[User::CURRENT_MISSION] = $flow->nextMission->id;
                }


                $achievement[User::WON_POINT] += $score->point;
            }
        } else {
            return [
                'uid' => $uid,
                'pass' => false,
                'message' => '計分資料有誤，無法取得該題資料。'
            ];
        }

        $this->saveAchievement($user, $achievement);

        return [
            'uid' => $uid,
            'pass' => true,
            'message' => null
        ];
    }

    /**
     * @param int $mission_id
     * @param Task $task
     * @return boolean
     */
    private function isLegalPathToAnswer(int $mission_id, Task $task): bool {
        $flow = MissionFlow::where('mission_id', $mission_id)->where('task_id', $task->id)->first();
        return (isset($flow) && $flow->mission_id == $task->mission->id);
    }

    /**
     * @param User $user
     * @param array $achievement
     */
    private function saveAchievement(User $user, array $achievement) {
        $user->achievement = $achievement;
        if ($user->isDirty('achievement')) {
            $user->save();
        }
    }

}
