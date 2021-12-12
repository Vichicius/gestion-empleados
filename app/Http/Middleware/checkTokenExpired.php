<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class checkTokenExpired
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
        $jdata = $request->getContent();
        $data = json_decode($jdata);

        try{

            $user = $request->get('userMiddleware');
            $lastlogin = $user->last_login;
            $tokenExpiration = date_add(new DateInterval('P1D'), $lastlogin);
            $now = new DateTime('now');

            if($tokenExpiration > $now){ //Si no ha caducado el token:
                
                return $next($request);

            }else{
                $response["status"] = 0;
                $response["msg"] = "Vuelve a iniciar sesion, el token ha expirado";
            }

        }catch(\Exception $e){
            $response["status"] = 0;
            $response["msg"] = $e;
        }

        return $response;
    }
}
