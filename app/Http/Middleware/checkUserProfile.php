<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;

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
        Si es RRHH o directivo return next y le paso el usuario
        si es empleado return response con un error
        */
        $jdata = $request->getContent();
        $data = json_decode($jdata);
        
        try{
            $userMiddleware = User::where('api_token', $data->api_token)->first();
            switch ($userMiddleware->puesto) {
                case 'empleado':
                    $permiso = 1;
                    break;
                case 'RRHH':
                    $permiso = 2;
                    break;
                case 'directivo':
                    $permiso = 3;
                    break;
                default:
                    throw new Exception();
                    break;
            }
            if($permiso>=2){
                $request->attributes->add(['userMiddleware' => $userMiddleware]);
                $request->attributes->add(['permiso' => $permiso]);
                return $next($request);
            }else{
                $response["status"] = 0;
                $response["msg"] = "No tienes permisos suficientes";
            }
        }catch(\Exception $e){
            $response["status"] = 0;
            $response["msg"] = "No has iniciado sesión";
            $response["Exception"] = $e;
        }
        return response()->json($response);
        
    }
}
