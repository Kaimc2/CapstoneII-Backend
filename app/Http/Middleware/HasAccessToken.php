<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasAccessToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->cookie('access_token')) {
            try {
                $request->headers->set('Authorization', 'Bearer ' . $request->cookie('access_token'));
            } catch (\Exception $e) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        }

        return $next($request);
    }
}
