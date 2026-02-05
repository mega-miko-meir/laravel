<?php

namespace App\Http\Middleware;

use DB;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    public function handle($request, Closure $next)
    {
        if (auth()->check()) {
            DB::table('activity_logs')->insert([
                'user_id' => auth()->id(),
                'url' => $request->path(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $next($request);
    }
}

