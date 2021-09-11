<?php

namespace App\Http\Controllers;

use App\Ticket;
use App\User;
use App\Http\Traits\ApiTrait;
use Illuminate\Http\Request;
use App\Http\Traits\AuthTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    use ApiTrait;
    use AuthTrait;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $register_mode = env('REGISTER_MODE', 'app');

        switch ($register_mode) {
            case 'app':
                $user = $this->registerWithDefaultPassword($request);
                break;
            case 'web':
                $user = $this->registerWithUserInfo($request);
                break;
            default:
                $this->returnApiResponse('內部設定錯誤', [], false, 500);
                break;
        }

        $credentials = [
            'uid' => $user->uid,
            'password' => $register_mode == 'app' ? $user->email : $request->password,
        ];

        return $this->respondWithToken($this->guard()->attempt($credentials));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $rules = [
            'uid' => 'required',
            'password' => 'required',
        ];
        $this->validate($request, $rules);

        $credentials = $request->only(['uid', 'password']);

        if (! $token = $this->guard()->attempt($credentials)) {
            return $this->return404Response();
        }

        return $this->respondWithToken($token);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function invite(Request $request)
    {
        $rules = [
            'uid' => 'required',
            'email' => 'required|string',
        ];
        $this->validate($request, $rules);

        $user = User::firstOrNew(['uid' => $request->uid]);
        $user->email = $request->email;
        $user->password = Hash::make($request->email);
        $user->save();

        $credentials = [
            'uid' => $user->uid,
            'password' => $user->email,
        ];

        if (! $token = $this->guard()->attempt($credentials)) {
            return $this->return404Response();
        }

        return $this->respondWithToken($token);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = $this->guard()->user();

        return ($user) ? $this->returnSuccess('Success.', $user) : $this->return404Response();
    }

    /**
     * Register account with default password
     * @param Request $request
     * @return \App\User
     */
    protected function registerWithDefaultPassword(Request $request): User
    {
        $rules = [
            'uid' => 'required|unique:users',
            'email' => 'required|string',
        ];
        $this->validate($request, $rules);
        $user = User::create($request->only(['uid', 'email']));
        $user->password = Hash::make($request->email);
        $user->save();

        return $user;
    }


    /**
     * Register account
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function registerWithUserInfo(Request $request): User
    {
        $rules = [
            'email' => 'required|unique:users,uid|string',
            'nickname' => 'required|string',
            'password' => 'required|string',
            'ticket_number' => [
                'required',
                'string',
                Rule::exists('tickets')->where(function ($query) {
                    $query->where('user_id', null)->where('disabled', 0);
                })
            ],
        ];
        $this->validate($request, $rules);

        DB::beginTransaction();

        try {
            $ticket = Ticket::where('ticket_number', $request->ticket_number)->firstOrFail();

            $user = User::create(
                [
                    'uid' => $request->email,
                    'nickname' => $request->nickname,
                    'email' => $request->email,
                ]
            );
            $user->password = Hash::make($request->password);
            $user->save();

            $ticket->user_id = $user->id;
            $ticket->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $user;
    }

    /**
     * Get the token array structure.
     * @param  string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return $this->returnSuccess('Success.', [
            'access_token' => $token,
            'token_type' => 'bearer',
        ]);
    }
}
