<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureFinanceEnabled
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(config('features.financeiro'), 404);

        return $next($request);
    }
}
