<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TahunAktif
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->tahun) {
            abort(403, 'Tahun anggaran belum ditentukan');
        }

        return $next($request);
    }
}
