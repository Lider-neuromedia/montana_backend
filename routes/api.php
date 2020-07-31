<?php

use Illuminate\Http\Request;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::apiResource('/catalogos', 'CatalogoController');
// Route::apiResource('/categorias', 'CategoriaController');
// Route::apiResource('/ciudades', 'CiudadController');
// Route::apiResource('/cilente-tienda', 'ClienteTiendaController');
// Route::apiResource('/comentarios', 'ComentarioController');
// Route::apiResource('/departamentos', 'DepartamentoController');
// Route::apiResource('/descuentos', 'DescuentoController');
// Route::apiResource('/encuestas', 'EncuestaController');
// Route::apiResource('/encuesta-pregunta', 'EncuestaPreguntaController');
// Route::apiResource('/estados', 'EstadoController');
// Route::apiResource('/formularios', 'FormularioController');
// Route::apiResource('/galeria-productos', 'GaleriaProductoController');
// Route::apiResource('/iva', 'IvaController');
// Route::apiResource('/marcas', 'MarcaController');
// Route::apiResource('/preguntas', 'PreguntaController');
// Route::apiResource('/productos', 'ProductoController');
// Route::apiResource('/tiendas', 'TiendaController');
// Route::post('/create-admin','UserController@createAdmin');
// Route::apiResource('/users', 'UserController');
// Route::post('/create-admin','UserController@createAdmin');
// Route::apiResource('/user-data', 'UserDataController');
// Route::apiResource('/permissions', 'PermissionController');


Route::apiResource('/roles', 'RolController');
Route::apiResource('/users', 'UserController');
// Route::post('/userdata', 'UserController@userData');
Route::apiResource('/userdata', 'UserDataController');


Route::group( [ 'middleware' => ['role:administrador'] ], function() {
    // Route::apiResource('/users', 'UserController');
});

Route::group( [ 'middleware' => ['permission:create user'] ], function() {
    #Route::apiResource('/users', 'UserController');
});


Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
  
    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });
});