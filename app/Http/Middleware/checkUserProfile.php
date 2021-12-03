<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class checkUserProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //comprobacion si tiene permisos (si es empleado, RRHH o directivo)
        /*
        Si es RRHH o directivo return next
        si es empleado return response con un error
        */
        return $next($user);
    }
}
