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

Route::middleware(["validar_permiso"])->group(function () {
    Route::prefix('users')->group(function(){
        Route::get('login', [controladorUsuarios::class, 'register']);
        Route::get('register', [controladorUsuarios::class, 'login'])->withoutMiddleware("validar_permiso"); //quitar la validacion del middleware
        Route::get('forgot-password', [controladorUsuarios::class, 'passRecovery'])->withoutMiddleware("validar_permiso");
        Route::get('list', [controladorUsuarios::class, 'employeeList']);
        Route::get('details/{$id}', [controladorUsuarios::class, 'employeeDetails']);
        Route::get('profile', [controladorUsuarios::class, 'viewOwnProfile']);
        Route::get('edit', [controladorUsuarios::class, 'editEmployee']);
    });

});

