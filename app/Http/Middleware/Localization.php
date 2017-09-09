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

        if (!$response->isSuccessful()) {
            $content = json_decode($response->getContent());

            if (isset($content) && property_exists($content, 'error')) {
                if ($response->getStatusCode() === 422) {

                    $message = [];

                    foreach ($content->error as $error) {
                        $message = array_merge($message, array_pluck($error, 'message'));
                    }

                    $message = implode($message, ', ');
                } else {
                    $message = trans('response.' . $content->error);
                }

                $response->setContent(json_encode([
                    'error' => $content->error,
                    'message' => $message
                ]));
            }
        }

        return $response;
    }
}
