<?php

namespace App\Http\Controllers;

use App\Mission;
use App\Reward;
use App\Http\Traits\ApiTrait;

class InfoController extends Controller
{
    use ApiTrait;

    const GAME_INFO = [
        'image' => 'https://lorempixel.com/640/480/technics/Faker/?66666',
        'title' => '歡迎登鑑',
        'title_e' => 'Welcome',
        'description' => '終於等到你了！歡迎加入 Mo 孃的宇宙冒險之旅。一起探索闖關，找尋我們的 One Piece 吧！',
        'description_e' => 'Welcome to MOPCON Game Field.',
    ];

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function showGameInfo()
    {
        return $this->returnSuccess('Success.', self::GAME_INFO);
    }
}
