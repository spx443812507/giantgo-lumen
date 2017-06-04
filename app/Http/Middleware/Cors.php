<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/4/21
 * Time: 上午11:54
 */

namespace App\Http\Middleware;


class Cors
{
    public function handle($request, \Closure $next)
    {
        //Intercepts OPTIONS requests
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }

        // Adds headers to the response
        $response->headers->set('Access-Control-Allow-Methods', 'HEAD, GET, POST, PUT, PATCH, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', $request->header('Access-Control-Request-Headers'));
        $response->headers->set('Access-Control-Allow-Origin', '*');

        // Sends it
        return $response;
    }
}