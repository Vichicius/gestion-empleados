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

Route::middleware(["validar_permiso", "validar_token"])->group(function () {
    Route::prefix('users')->group(function(){
        Route::post('register', [controladorUsuarios::class, 'register']);
        Route::put('login', [controladorUsuarios::class, 'login'])->withoutMiddleware(["validar_permiso", "validar_token"]); //quitar la validacion del middleware
        Route::put('forgot-password', [controladorUsuarios::class, 'passRecovery'])->withoutMiddleware(["validar_permiso", "validar_token"]);
        Route::get('list', [controladorUsuarios::class, 'employeeList']);
        Route::get('details/{$id}', [controladorUsuarios::class, 'employeeDetails']);
        Route::get('profile', [controladorUsuarios::class, 'viewOwnProfile']);
        Route::put('edit', [controladorUsuarios::class, 'editEmployee']);
    });

});

