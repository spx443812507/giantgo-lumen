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

            $content = $response->getContent();

            $content = json_decode($content);

            if (property_exists($content, 'error')) {
                $response->setContent(json_encode([
                    'error' => $content->error,
                    'message' => trans('response.' . $content->error)
                ]));
            }
        }

        return $response;
    }
}
