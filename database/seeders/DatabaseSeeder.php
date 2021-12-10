<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $user = new User;
        //repito varias veces para ajustar la proporcion de puestos
        $puestosArr = ["empleado", "empleado", "empleado", "RRHH", "RRHH", "directivo"];
        //Crear el admin
        $user->name = "admin";
        $user->email = "admin@admin.admin";
        $password = "admin";
        $user->password = Hash::make($password);
        $user->puesto = "directivo";
        $user->salario = 0;
        $user->biografia = "Soy admin";
        $user->save();

        //Crear usuarios de prueba
        for ($i=0; $i < 10; $i++) { 
            $user = new User;
            $user->name = Str::random(5);
            $user->email = Str::random(8)."@gmail.com";
            $password = Str::random(8);
            $user->password = Hash::make($password);
            $user->puesto = $puestosArr[array_rand($puestosArr)];
            $user->salario = rand(15,100)*1000;
            $user->biografia = Str::random(20);
            $user->save();
        }


    }
}
