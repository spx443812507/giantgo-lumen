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
        $acceptLanguage = $request->header('accept-language');

        if ($acceptLanguage) {
            $language = substr(current(explode(',', $acceptLanguage)), 0, 2);
            app('translator')->setLocale($language);
        } else {
            app('translator')->setLocale('zh');
        }

        $response = $next($request);

        //escape parameters error
        if (!$response->isSuccessful() && $response->getStatusCode() !== 422) {

            $content = $response->getContent();

            $content = json_decode($content);

            if (isset($content) && property_exists($content, 'error')) {
                $response->setContent(json_encode([
                    'error' => $content->error,
                    'message' => trans('response.' . $content->error)
                ]));
            }
        }

        return $response;
    }
}
