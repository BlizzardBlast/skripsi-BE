<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {

        // $headers = [
        //     'Access-Control-Allow-Origin' => '*',
        //     'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        //     'Access-Control-Allow-Headers' => 'X-CSRF-Token, X-Requested-With, Accept, Accept-Version, Content-Length, Content-MD5, Content-Type, Date, X-Api-Version',
        //     'Access-Control-Max-Age' => '86400',
        //     'Access-Control-Allow-Credentials' => 'true'
        // ];

        // if ($request->isMethod('OPTIONS')) {
        //     return response()->json('OK', 200, $headers);
        // }

        // $response = $next($request);

        // foreach ($headers as $key => $value) {
        //     $response->header($key, $value);
        // }

        // return $response;

        // Define the allowed origin for your frontend
        $allowedOrigins = ['https://kofebin.vercel.app', 'http://localhost:5173'];

        // Check if the request origin is allowed
        $origin = $request->headers->get('Origin');
        if (in_array($origin, $allowedOrigins)) {
            $headers = [
                'Access-Control-Allow-Origin' => $origin,
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Credentials' => 'true', // Enable credentials
                'Access-Control-Max-Age' => '86400', // 24 hours
            ];
        } else {
            $headers = [];
        }

        if ($request->isMethod('OPTIONS')) {
            return response('OK', 200, $headers);
        }

        $response = $next($request);

        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}
