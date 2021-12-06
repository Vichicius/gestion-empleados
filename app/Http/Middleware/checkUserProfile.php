<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use app\Models\User;
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
        $response = "";
        try{
            $user = User::where('api_token')->first();
            switch ($user->puesto) {
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
                return $next($user,$permiso);
            }else{
                $response["status"] = 0;
                $response["msg"] = "No tienes permisos suficientes";
            }
        }catch(\Exception $e){
            $response["status"] = 0;
            $response["msg"] = "No has iniciado sesión";
            $response["Exception"] = $e;
        }
        return $response;
        
    }
}
