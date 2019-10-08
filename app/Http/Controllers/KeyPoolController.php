<?php

namespace App\Http\Controllers;

use App\KeyPool;
use App\Http\Traits\ApiTrait;
use Illuminate\Http\Request;

class KeyPoolController extends Controller
{
    use ApiTrait;

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $key = KeyPool::create($request->only([
            'key',
            'type',
            'note',
            'slug',
            'account',
            'passwd',
        ]));

        return $this->returnSuccess('Store success.', $key);
    }
}
