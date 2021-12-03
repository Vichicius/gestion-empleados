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
    Route::get('users/login', [controladorUsuarios::class, 'register']);
    Route::get('users/registrar', [controladorUsuarios::class, 'login']);
});

