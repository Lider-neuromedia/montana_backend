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

Route::get('/unauthenticated', function () {
    return response()->json([
        'response' => 'error',
        'status' => 403,
        'message' => 'Token vencido o invalido.',
    ], 403);
})->name('unauthenticated');

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('monitoreo', 'MonitoreoController@index');
});

Route::group(['middleware' => ['permission:create user']], function () {
    //
});

Route::group(['namespace' => 'Auth'], function () {

    Route::post('password/email', 'PasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'PasswordController@reset');

});

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
        Route::get('/getUserSesion', 'AuthController@getUserSesion');
    });
});

Route::group(['middleware' => 'auth:api'], function () {

    Route::post('devices', 'DevicesController@post');
    Route::get('/dashboard-resumen', 'AuthController@dashboardResumen');

    Route::apiResource('/roles', 'RolController');

    Route::apiResource('/asignar-cliente', 'VendedorClienteController', ['only' => ['index', 'store', 'show']]);
    Route::get('/vendedor-asignado', 'VendedorClienteController@vendedorAsignado');

    // USUARIOS
    Route::apiResource('/users', 'UserController');
    Route::post('/update-user', 'UserController@updateUser');
    Route::post('/delete-users', 'UserController@destroyUsers');
    Route::get('/user-rol/{id}', 'UserController@getForRole');

    // ADMINISTRADORES
    Route::get('/admins', 'UserController@getAdmins');
    Route::get('/admin/{id}', 'UserController@getAdmin');

    // VENDEDORES
    Route::get('/vendedores', 'UserController@getVendedores');
    Route::get('/vendedor/{id}', 'UserController@getVendedor');
    Route::get('/clientes-asignados/{id}', 'UserController@assignedCustomers');
    Route::get('/searchClientes', 'UserController@searchClientes');
    Route::post('/update-vendedor/{id}', 'UserController@updateVendedor');
    Route::get('updateAsignClient/{cliente}/{vendedor}/{action}', 'UserController@updateAsignClient');

    // CLIENTES
    Route::get('/clientes', 'UserController@getClientes');
    Route::get('/cliente/{id}', 'UserController@getCliente');
    Route::post('/update-cliente/{id}', 'UserController@updateClient');
    Route::get('/searchVendedor', 'UserController@searchVendedor');
    Route::get('updateAsignVend/{cliente}/{vendedor}/{action}', 'UserController@updateAsignVend');
    Route::post('newTienda/{cliente}', 'UserController@newTienda');

    // CATALOGOS
    Route::apiResource('/catalogos', 'CatalogoController');
    Route::get('consumerCatalogos', 'CatalogoController@consumerCatalogos');

    // PRODUCTO
    Route::get('/productos/{catalogo}', 'ProductoController@index');
    Route::get('/producto/{id}', 'ProductoController@detalleProducto');
    Route::get('/marcas', 'ProductoController@getMarcas');
    Route::post('/productos', 'ProductoController@store');
    Route::put('/producto/{id}', 'ProductoController@update');
    Route::delete('/producto/{id}', 'ProductoController@destroy');

    // SHOW ROOM
    Route::get('getProductsShowRoom', 'ProductoController@getProductsShowRoom');

    // PEDIDOS
    Route::apiResource('/pedidos', 'PedidoController');
    Route::get('/recursos-crear-pedido', 'PedidoController@resourcesCreate');
    Route::get('tiendas-cliente/{id}', 'PedidoController@tiendaCliente');
    Route::get('generate-code', 'PedidoController@generateCodePedido');
    Route::post('change-state-pedido', 'PedidoController@changeState');
    Route::post('crear-novedad', 'PedidoController@storeNovedades');
    Route::get('edit-pedido/{id}', 'PedidoController@edit');
    Route::post('update-pedido', 'PedidoController@update');
    Route::get('export-pedido', 'PedidoController@exportPedido');

    // DESCUENTOS
    Route::get('getPedidoWithCode/{code}', 'PedidoController@getPedidoWithCode');
    Route::get('changeDescuentoPedido/{pedido}/{descuento}', 'PedidoController@changeDescuentoPedido');

    // TIENDAS
    Route::apiResource('tiendas', 'TiendaController', ['store', 'update']);
    route::post('delete-tiendas', 'TiendaController@destroy');

    // ENCUESTAS
    Route::apiResource('encuestas', 'EncuestaController', ['index', 'store', 'update']);
    Route::get('editEncuesta/{id}', 'EncuestaController@edit');

    // INTEGRACIÓN ENCUESTAS - PRODUCTOS
    Route::get('getPreguntas/{catalogo}', 'EncuestaController@getPreguntas');
    Route::get('getValoraciones/{catalogo}', 'EncuestaController@getValoraciones');
    Route::get('getProductoValoraciones/{producto}', 'EncuestaController@getProductoValoraciones');

    Route::post('storeRespuestas', 'EncuestaController@storePreguntas');
    Route::get('eliminarPregunta/{pregunta}', 'EncuestaController@destroyPregunta');

    // AMPLIACION CUPO
    Route::apiResource('ampliacion-cupo', 'AmpliacionCupoController', ['only' => ['index', 'store', 'update']]);
    Route::get('getUserSmall/{rol_id}', 'AmpliacionCupoController@getUserSmall');
    Route::get('cambiar-estado/{solicitud}/{estado}', 'AmpliacionCupoController@changeState');

    // PQRS
    Route::apiResource('pqrs', 'PqrsController');
    Route::post('newMessage', 'PqrsController@NewMessage');
    Route::get('changeState/{id}/{state}', 'PqrsController@changeState');
    Route::get('getPqrsUser', 'PqrsController@getPqrsUserSesion');

    Route::post('batch/importar-marcas', 'BatchDataController@importarMarcas');
    Route::post('batch/importar-productos', 'BatchDataController@importarProductos');
    Route::post('batch/importar-vendedores', 'BatchDataController@importarVendedores');
    Route::post('batch/importar-clientes', 'BatchDataController@importarClientes');
    Route::post('batch/importar-cartera', 'BatchDataController@importarCartera');
});
