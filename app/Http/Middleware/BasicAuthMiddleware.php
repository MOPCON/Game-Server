<?php

namespace App\Http\Middleware;

use Closure;
use App\KeyPool;

class BasicAuthMiddleware
{
    /**
      * Handle an incoming request.
      *
      * @param  \Illuminate\Http\Request  $request
      * @param  \Closure  $next
      * @return mixed
      */
    public function handle($request, Closure $next)
    {
        $headers = array('WWW-Authenticate' => 'Basic');

        $key = KeyPool::where('slug', $request->route('slug'))->firstOrFail();
        if ($request->getUser() != $key->account || $request->getPassword() != $key->passwd) {
            return response('Invalid Access.', 401, $headers);
        }
        return $next($request);
    }
}
