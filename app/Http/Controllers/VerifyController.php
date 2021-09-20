<?php

namespace App\Http\Controllers;

use App\User;
use App\Task;
use App\Reward;
use App\KeyPool;
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
        if (! $request->filled(['uid', 'vKey'])) {
            return $this->return400Response($this->returnWrongAnswerMessage());
        }

        if (! $this->checkKey($request->input('uid'), $request->input('vKey'), $vType)) {
            return $this->return400Response($this->returnWrongAnswerMessage());
        }

        $uid = $request->input('uid');
        $user = $this->guard()->user();
        $achievement = $user->achievement;

        switch ($vType) {
            case KeyPool::TYPE_TASK:
                $task = Task::where('uid', $uid)->firstOrFail();
                $task_id = $task->id;
                $taskCollection = collect($achievement[User::COMPLETED_TASK]);
                if ($taskCollection->where(['task_id', $task_id])->isEmpty()) {
                    $score = $user
                        ->scores()
                        ->get()
                        ->firstWhere('task_id', $task->id);
                    if ($score) {
                        if ($score->pass == 0) {
                            $score->pass = 1;
                            $score->save();

                            array_push(
                                $achievement[User::COMPLETED_TASK],
                                [
                                    'mission_id' => $score->mission_id,
                                    'task_id' => $score->task_id,
                                ]
                            );
                            $achievement[User::WON_POINT] += $score->point;
                        }
                    } else {
                        return $this->return400Response('計分資料有誤，無法取得該題資料。');
                    }
                }

                break;

            case KeyPool::TYPE_REWARD:
                $reward = Reward::where('uid', $uid)->firstOrFail();
                if (! $reward->redeemable) {
                    return $this->return400Response("獎品已兌換完畢囉。");
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
                    return $this->return400Response('驗證碼輸入錯誤，請檢查後重新輸入。');
                }

                if ($newCollection) {
                    $achievement[User::WON_REWARD] = $newCollection->all();
                }

                break;
        }

        $user->achievement = $achievement;
        if ($user->isDirty('achievement')) {
            $user->save();
        }

        return $this->returnSuccess('Success.');
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
            case KeyPool::TYPE_TASK:
                $result = $this->checkTaskKey($uid, $key);
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
    private function checkTaskKey(string $uid, string $key): bool {

        $result = false;

        $input_type = env('ANSWER_INPUT_TYPE', 'qrcode');

        $check_timestamp = ($input_type == 'qrcode');

        $task = Task::where('uid', $uid)->firstOrFail();
        $vkey = $task->KeyPool->key;

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
            return '你的答案錯誤，再檢查一下！';
        }

    }
}
