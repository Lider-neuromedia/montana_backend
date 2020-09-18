<?php


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
// Route::apiResource('/users', 'UserController');
// Route::post('/userdata', 'UserController@userData');

Route::apiResource('/asignar-cliente', 'VendedorClienteController');

Route::group( [ 'middleware' => ['role:administrador'] ], function() {
    // Route::apiResource('/users', 'UserController');
});

Route::group( [ 'middleware' => ['permission:create user'] ], function() {
    #Route::apiResource('/users', 'UserController');
});

Route::apiResource('/users', 'UserController');
Route::post('/update-user', 'UserController@updateUser');

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
  
    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });
});

Route::group(['middleware' => 'auth:api'], function() {
    //USUARIOS 
    Route::delete('/delete-users', 'UserController@destroyUsers');
    Route::get('/user-rol/{id}','UserController@getForRole');
    // Route::post('/delete-user', 'UserController@destroyUsers');
    
    //VENDEDORES. 
    Route::get('/vendedores','UserController@getVendedores');
    Route::get('/vendedor/{id}','UserController@getVendedor');
    Route::get('/clientes-asignados/{id}', 'UserController@assignedCustomers');
    
    // CLIENTES.
    Route::get('/clientes','UserController@getClientes');
    Route::get('/cliente/{id}','UserController@getCliente');
    
    // ADMINISTRADORES.
    Route::get('/admins', 'UserController@getAdmins');
    Route::get('/admin/{id}', 'UserController@getAdmin');

    // CATALOGOS.
    Route::apiResource('/catalogos', 'CatalogoController');
    
    // PRODUCTO.
    Route::get('/productos/{catalogo}', 'ProductoController@index');
    Route::post('/productos', 'ProductoController@store');

});