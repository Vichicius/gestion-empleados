<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use app\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuariosController extends Controller
{
    //
    public function register(Request $req){
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        if($data->name && $data->email && $data->password && $data->puesto && $data->salario && $data->biografia){
            try{
                $user = new User;
                $user->name = $data->name;
                $user->email = $data->email;
                if(preg_match("^/(?=.*[a-z)(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/", $data->password)){
                    $user->password = Hash::make($data->password);
                }else{
                    throw new Exception('Contraseña insegura.');
                }
                $user->puesto = $data->puesto;
                $user->salario = $data->salario;
                $user->biografia = $data->biografia;
                $user->save();
                $response["status"]=1;
                $response["msg"]="Guardado con éxito";
            }catch(\Exception $e){
                $response["status"]=0;
                $response["msg"]="Error al intentar guardar el usuario: ".$e;
            }
            
        }else{
            $response["status"]=0;
            $response["msg"]="introduce name, email, password, puesto, salario, biografia";
        }

        //pruebas
        $response["pruebas"] = $req->permiso;
    }
    public function login(Request $req){
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $user = User::where('email',$data->email)->first();

        //comprobar que existe el email
        if($user){
            //comprobar que está bien la contraseña
            if(Hash::check($data->password, $user->password)){
                $user->api_token = Hash::make(now().$user->email);
                $user->save();
                $response["status"] = 1;
                $response["msg"] = "sesion iniciada";
                $response["user"] = $user;
            }else{
                $response["status"] = 0;
                $response["msg"] = "Contraseña incorrecta";
            }
        }else{
            $response["status"] = 0;
            $response["msg"] = "No se encuentra el email";
        }
        return $response;

        
    }

    public function passRecovery(Request $req){
        $jdata = $req->getContent();
        $data = json_decode($jdata);
        
        /*me pasa el email
        pillo el usuario
        le cambio la contraseña a un hash aleatorio
        guardo la pass
        se la envio por email
        */

        $user = User::where('email',$data->email);
        if($user){
            $newPass = Str::random(16);
            $user->password = Hash::create($newPass);
            $user->save();
            $response["status"] = 1;
            $response["msg"] = "Se ha cambiado la contraseña";
            $response["contenido del email"] = "La contraseña es: ".$newPass;

        }else{
            $response["status"] = 0;
            $response["msg"] = "No se encuentra el email";
        }
        return $response;

    }

    public function employeeList(Request $req){
        $jdata = $req->getContent();
        $data = json_decode($jdata);
        
        /*
        Muestra Nombre, puesto, salario
        De los que tienen menos permisos que el que lo mira
        los de rango 1 no pueden (empleados)
        */

        $empleados = User::where('puesto',"empleado")->get();
        $i = 1; //utilizo i en vez de key porque si no se sobreescribe al introducir los de RRHH
        foreach ($empleados as $key => $empleado) {
            $response[$i]["Nombre"] = $empleado->name;
            $response[$i]["Puesto"] = $empleado->puesto;
            $response[$i]["Salario"] = $empleado->salario;
            $i++;
        }
        if($req->get("permiso") >2 ){//si su permiso es mayor que RRHH
            $empleados = User::where('puesto',"RRHH")->get();
            foreach ($empleados as $key => $empleado) {
                $response[$i]["Nombre"] = $empleado->name;
                $response[$i]["Puesto"] = $empleado->puesto;
                $response[$i]["Salario"] = $empleado->salario;
                $i++;
            }
        }

        return $response;

    }

    public function employeeDetails(Request $req, int $id){
        $jdata = $req->getContent();
        $data = json_decode($jdata);
        
        /*
        Muestra Nombre, email, puesto, biografía, salario
        De los que tienen menos permisos que el que lo mira
        los de rango 1 no pueden (empleados)
        */

        $empleado = User::find($id);
        if($empleado){
            switch ($empleado->puesto) {
                case 'empleado':
                    $permisoDelID = 1;
                    break;
                case 'RRHH':
                    $permisoDelID = 2;
                    break;
                case 'directivo':
                    $permisoDelID = 3;
                    break;
                default:
                    $permisoDelID = 0;
                    break;
            }
            if($req->get("permiso") < $permisoDelID){
                $response["Nombre"] = $empleado->name;
                $response["Email"] = $empleado->email;
                $response["Puesto"] = $empleado->puesto;
                $response["Biografía"] = $empleado->biografia;
                $response["Salario"] = $empleado->salario;
            }else{
                $response["status"] = 0;
                $response["msg"] = "No tienes permisos suficientes para ver los detalles de esta persona";
            }
        }
        
        return $response;

    }
    
}
