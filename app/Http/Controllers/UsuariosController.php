<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use DateTime;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderShipped;
use Illuminate\Support\Str;


class UsuariosController extends Controller
{
    //
    public function register(Request $req){ //Pide: api_token, name, email, password, puesto, salario y biografia
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        if($data->name && $data->email && $data->password && $data->puesto && $data->salario && $data->biografia){
            try{
                $user = new User;
                $user->name = $data->name;
                $user->email = $data->email;
                if(preg_match("/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/", $data->password)){
                    $user->password = Hash::make($data->password);
                }else{
                    $response["status"]=0;
                    $response["msg"]="Contraseña insegura. Mínimo: 1 Mayúscula, 1 minúscula, 1 caracter especial y 1 número";
                    return response()->json($response);
                    //throw new Exception('Contraseña insegura.');
                }
                $user->puesto = $data->puesto;
                $user->salario = $data->salario;
                $user->biografia = $data->biografia;
                
                $allTokens = User::pluck('api_token')->toArray();

                do {
                    $user->api_token = Hash::make(now().$user->email);
                } while (in_array($user->api_token, $allTokens)); //En bucle mientras que el apitoken esté duplicado
                
                $user->last_login = new DateTime('now');
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

        
        return response()->json($response);
    }

    public function login(Request $req){ //Pide: email y password
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $user = User::where('email',$data->email)->first();

        //comprobar que existe el email
        if($user){
            //comprobar que está bien la contraseña
            if(Hash::check($data->password, $user->password)){
                
                $allTokens = User::pluck('api_token')->toArray();

                do {
                    $user->api_token = Hash::make(now().$user->email);
                } while (in_array($user->api_token, $allTokens)); //En bucle mientras que el apitoken esté duplicado
                
                $user->last_login = new DateTime('now');
                $user->save();
                $response["status"] = 1;
                $response["msg"] = "sesion iniciada";
                $response["api_token"] = $user->api_token;
            }else{
                $response["status"] = 0;
                $response["msg"] = "Contraseña incorrecta";
            }
        }else{
            $response["status"] = 0;
            $response["msg"] = "No se encuentra el email";
        }
        return response()->json($response);

        
    }

    public function passRecovery(Request $req){//Pide: email
        $jdata = $req->getContent();
        $data = json_decode($jdata);
        
        /*me pasa el email
        pillo el usuario
        le cambio la contraseña a un hash aleatorio
        guardo la pass
        se la envio por email
        */

        $user = User::where('email',$data->email)->first();
        if($user){
            $newPass = Str::random(16);
            $user->password = Hash::make($newPass); //hacerla temporal    
            $user->save();
            Mail::to($user->email)->send(new OrderShipped (
                $newPass
            ));
            $response["status"] = 1;
            $response["msg"] = "Se ha cambiado la contraseña";
            $response["contenido del email"] = "La contraseña es: ".$newPass;

        }else{
            $response["status"] = 0;
            $response["msg"] = "No se encuentra el email";
        }
        return response()->json($response);
    }

    public function employeeList(Request $req){//Pide: api_token
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
            $response[$i]["id"] = $empleado->id;
            $response[$i]["Nombre"] = $empleado->name;
            $response[$i]["Puesto"] = $empleado->puesto;
            $response[$i]["Salario"] = $empleado->salario;
            $i++;
        }
        if($req->get("permiso") > 2 ){//si su permiso es mayor que RRHH
            $empleados = User::where('puesto',"RRHH")->get();
            foreach ($empleados as $key => $empleado) {
                $response[$i]["id"] = $empleado->id;
                $response[$i]["Nombre"] = $empleado->name;
                $response[$i]["Puesto"] = $empleado->puesto;
                $response[$i]["Salario"] = $empleado->salario;
                $i++;
            }
        }

        return response()->json($response);

    }

    public function employeeDetails(Request $req){//Pide: api_token y id (del empleado)
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        /*
        Muestra Nombre, email, puesto, biografía, salario
        De los que tienen menos permisos que el que lo mira
        los de rango 1 no pueden (empleados)
        */

        $empleado = User::find($data->id);
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
            if($req->get("permiso") > $permisoDelID){
                $response["id"] = $empleado->id;
                $response["Nombre"] = $empleado->name;
                $response["Email"] = $empleado->email;
                $response["Puesto"] = $empleado->puesto;
                $response["Biografía"] = $empleado->biografia;
                $response["Salario"] = $empleado->salario;
            }else{
                $response["status"] = 0;
                $response["msg"] = "No tienes permisos suficientes para ver los detalles de esta persona";
            }
        }else{
            $response["status"] = 0;
            $response["msg"] = "No se encuentra el empleado";
        }
        
        return response()->json($response);
    }

    public function viewOwnProfile(Request $req){//Pide: api_token
        $jdata = $req->getContent();
        $data = json_decode($jdata);
        
        /*
            Muestra Nombre, Email, puesto, Biografía, salario
            Del mismo usuario que accede
        */

        $user = $req->get("userMiddleware");
        if($user){
            $response["id"] = $user->id;
            $response["Nombre"] = $user->name;
            $response["Email"] = $user->email;
            $response["Puesto"] = $user->puesto;
            $response["Biografía"] = $user->biografia;
            $response["Salario"] = $user->salario;
        }else{
            $response["status"] = 0;
            $response["msg"] = "No has iniciado sesion (falta api_token)";
        }
        return response()->json($response);

    }
    
    public function editEmployee(Request $req){//Pide: api_token, emailEmpleado y los campos a editar del empleado
        $jdata = $req->getContent();
        $data = json_decode($jdata);
        
        /*
            Editar Nombre, Email, puesto, Biografía, salario, password
            De los que tienen menos permisos que él o se puede editar a él mismo.
        */

        //saber id del editor
        //saber id del editado mediante su email

        //comparar permisos

        //hacer los cambios y guardarlos

        $editor = $req->get("userMiddleware");
        $editado = User::where('email',$data->emailEmpleado)->first();

        if($editor && $editado){
            try{
                switch ($editado->puesto) {
                    case 'empleado':
                        $permisoDelEditado = 1;
                        break;
                    case 'RRHH':
                        $permisoDelEditado = 2;
                        break;
                    case 'directivo':
                        $permisoDelEditado = 3;
                        break;
                    default:
                        $permisoDelEditado = 0;
                        break;
                }
                //pasa si se intenta editar a alguien con menos permisos o si se intenta editar un directivo a él mismo
                if( ($editor->id == $editado->id && $req->get("permiso") == 3) || $req->get("permiso") >  $permisoDelEditado){ 
                    if(isset($data->name)) $editado->name = $data->name;
                    if(isset($data->email)) $editado->email = $data->email;
                    if(isset($data->puesto)) $editado->puesto = $data->puesto;
                    if(isset($data->biografia)) $editado->biografia = $data->biografia;
                    if(isset($data->salario)) $editado->salario = $data->salario;
                    if(isset($data->password)) $editado->password = $data->password;
                    $editado->save();
    
                    $response["status"] = 1;
                    $response["msg"] = "Usuario editado correctamente";
                    $response["user"] = $editado;
    
                }else{
                    $response["status"] = 0;
                    $response["msg"] = "No tienes permisos suficientes";
                }
            }catch(\Exception $e){
                $response["status"] = 0;
                $response["msg"]="Error: ".$e;
            }
            
        }else{
            $response["status"] = 0;
            $response["msg"] = "No se encuentra el email del empleado";

        }
        return response()->json($response);

    }

}
