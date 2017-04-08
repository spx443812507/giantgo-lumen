<?php

namespace App\Http\Middleware;

use Closure;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        app('translator')->setLocale('zh');

        $response = $next($request);

        if (!$response->isSuccessful()) {

            $error = $response->getData();

            if (property_exists($error, 'error')) {
                $response->setData([
                    'error' => $error->error,
                    'message' => trans('response.' . $error->error)
                ]);
            }
        }

        return $response;
    }
}
