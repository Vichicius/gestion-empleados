<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UsuariosController as controladorUsuarios;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


//quitado validar token para que al probar no sea muy pesado tener que estar logeando para renovar el token

Route::post('register', [controladorUsuarios::class, 'register']);
Route::put('login', [controladorUsuarios::class, 'login']); //quitar la validacion del middleware
Route::put('forgot-password', [controladorUsuarios::class, 'passRecovery']);

Route::middleware(["validar_permiso"])->group(function () {
    Route::put('list', [controladorUsuarios::class, 'employeeList']);
    Route::put('edit', [controladorUsuarios::class, 'editEmployee']);
});
Route::middleware(["validar_token"])->group(function () {
    Route::put('details', [controladorUsuarios::class, 'employeeDetails']);
    Route::get('profile', [controladorUsuarios::class, 'viewOwnProfile']);
});
