<?php

namespace App\Http\Controllers;

use App\Mission;
use App\Http\Traits\ApiTrait;
use Illuminate\Http\Request;

class MissionController extends Controller
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
        $mission = Mission::create($request->only([
            'name',
            'name_e',
            'description',
            'description_e',
            'image',
            'open',
            'point',
        ]));

        return $this->returnSuccess('Store success.', $mission);
    }
}
