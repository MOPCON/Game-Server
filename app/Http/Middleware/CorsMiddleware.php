<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class CorsMiddleware
{
    // 允許要求的 Origin
    protected $allowOrigin = [
        'http://localhost:8080',
        'http://localhost:4200',
        'https://game.mopcon.org'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $origin = $request->header('Origin');

        // 判斷是否為合法來源
        $isAllowOrigin = in_array($origin, $this->allowOrigin);

        // 判斷是否為 HTTP OPTIONS method
        $isOptions = $request->isMethod('OPTIONS');

        if (!$isAllowOrigin && $isOptions) {
            // 非法來源
            return new Response('', Response::HTTP_FORBIDDEN);
        }

        if ($isOptions) {
            // 合法來源的預檢請求
            $response = new Response('', Response::HTTP_OK);
        } else {
            $response = $next($request);
        }

        // 設定 Header
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Methods', 'OPTIONS, GET, POST');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, Authorization');
        return $response;
    }
}
