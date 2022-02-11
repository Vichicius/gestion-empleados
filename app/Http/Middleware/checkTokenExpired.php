<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use DateTime;
use DateInterval;

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
            if(!isset($user)){//Por si en alguna ruta me salto el primer middleware (como en el de ver perfil propio)
                $user = User::where('api_token', $data->api_token)->first();
                $request->attributes->add(['userMiddleware' => $user]);
            }

            $lastlogin = new DateTime($user->last_login);
            $tokenExpiration = date_add($lastlogin, new DateInterval('P1D'));
            $now = new DateTime('now');

            if($tokenExpiration > $now){ //Si no ha caducado el token:

                return $next($request);

            }else{
                $response["status"] = 0;
                $response["msg"] = "Vuelve a iniciar sesion, el token ha expirado";
            }

        }catch(\Exception $e){
            $response["status"] = 0;
            $response["archivo"] = "checkTokenExpired";
            $response["msg"] = $e;
        }

        return response()->json($response);
    }
}
