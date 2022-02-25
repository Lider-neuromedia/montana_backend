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

Route::group(['namespace' => 'Auth'], function () {

    Route::post('password/email', 'PasswordController@sendResetLinkEmail'); // DOC
    Route::post('password/reset', 'PasswordController@reset'); // DOC

});

// AUTH
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'AuthController@login'); // DOC
    Route::post('signup', 'AuthController@signup'); // DOC

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('logout', 'AuthController@logout'); // DOC
        Route::get('user', 'AuthController@user'); // DOC
        Route::get('/getUserSesion', 'AuthController@getUserSesion'); // DOC
    });
});

Route::group(['middleware' => 'auth:api'], function () {

    // OTROS
    Route::post('devices', 'DevicesController@post'); // DOC
    Route::get('/dashboard-resumen', 'ResumenController@dashboardResumen'); // DOC

    // USUARIOS
    Route::apiResource('/users', 'UserController', ['only' => ['index', 'store']]); // DOC
    Route::post('/update-user/{id}', 'UserController@actualizarUsuario'); // DOC
    Route::post('/delete-users', 'UserController@eliminarUsuarios'); // DOC
    Route::get('/user-rol/{rol_id}', 'UserController@usuariosPorRol'); // DOC
    Route::get('/roles', 'UserController@roles'); // DOC

    // ADMINISTRADORES
    Route::get('/admins', 'UserController@administradores'); // DOC
    Route::get('/admin/{id}', 'UserController@administrador'); // DOC

    // VENDEDORES
    Route::get('/vendedores', 'UserController@vendedores'); // DOC
    Route::get('/vendedor/{id}', 'UserController@vendedor'); // DOC
    Route::get('/searchClientes', 'UserController@buscarClientes'); // DOC
    Route::get('/clientes-asignados/{vendedor_id}', 'UserController@clientesAsignados'); // DOC
    Route::post('vendedores/{vendedor_id}/tiendas/{tienda_id}/asignar', 'UserController@asignarVendedorTienda'); // DOC
    Route::post('vendedores/{vendedor_id}/tiendas/{tienda_id}/quitar', 'UserController@quitarVendedorTienda'); // DOC

    // CLIENTES
    Route::get('/clientes', 'UserController@clientes'); // DOC
    Route::get('/cliente/{id}', 'UserController@cliente'); // DOC
    Route::get('/searchVendedor', 'UserController@buscarVendedor'); // DOC
    Route::get('/vendedores-asignados/{cliente_id}', 'UserController@vendedoresAsignados'); // DOC

    // CATALOGOS
    Route::apiResource('/catalogos', 'CatalogoController', ['only' => ['index', 'store', 'show', 'update', 'destroy']]); // DOC
    Route::get('consumerCatalogos', 'CatalogoController@catalogosActivos'); // DOC

    // PRODUCTO
    Route::get('/marcas', 'ProductoController@marcas'); // DOC
    Route::get('getProductsShowRoom', 'ProductoController@productosShowRoom'); // DOC
    Route::post('/productos', 'ProductoController@store'); // DOC
    Route::get('/productos/{catalogo}', 'ProductoController@index'); // DOC
    Route::get('/producto/{producto}', 'ProductoController@show'); // DOC
    Route::match(['put', 'patch'], '/producto/{producto}', 'ProductoController@update'); // DOC
    Route::delete('/producto/{producto}', 'ProductoController@destroy'); // DOC

    // PEDIDOS
    Route::apiResource('/pedidos', 'PedidoController');
    Route::get('/recursos-crear-pedido', 'PedidoController@resourcesCreate');
    Route::get('generate-code', 'PedidoController@generateCodePedido');
    Route::post('change-state-pedido', 'PedidoController@changeState');
    Route::post('crear-novedad', 'PedidoController@storeNovedades');
    Route::get('edit-pedido/{id}', 'PedidoController@edit');
    Route::post('update-pedido', 'PedidoController@update');
    Route::get('export-pedido', 'PedidoController@exportPedido');
    Route::get('getPedidoWithCode/{code}', 'PedidoController@getPedidoWithCode');
    Route::get('changeDescuentoPedido/{pedido}/{descuento}', 'PedidoController@changeDescuentoPedido');

    // TIENDAS
    Route::apiResource('tiendas', 'TiendaController', ['only' => ['store', 'update', 'show']]);
    Route::get('tiendas-cliente/{cliente_id}', 'TiendaController@clienteTiendas');
    Route::post('newTienda/{cliente}', 'TiendaController@nuevaTienda');
    route::post('delete-tiendas', 'TiendaController@eliminarTiendas');

    // AMPLIACION CUPO
    Route::apiResource('ampliacion-cupo', 'AmpliacionCupoController', ['only' => ['index', 'store', 'update']]);
    Route::get('getUserSmall/{rol_id}', 'AmpliacionCupoController@usersByRole');
    Route::get('cambiar-estado/{solicitud}/{estado}', 'AmpliacionCupoController@changeState');

    // IMPORTAR DB
    Route::post('batch/importar-marcas', 'BatchDataController@importarMarcas');
    Route::post('batch/importar-productos', 'BatchDataController@importarProductos');
    Route::post('batch/importar-vendedores', 'BatchDataController@importarVendedores');
    Route::post('batch/importar-clientes', 'BatchDataController@importarClientes');
    Route::post('batch/importar-cartera', 'BatchDataController@importarCartera');

    // // ENCUESTAS
    // Route::apiResource('encuestas', 'EncuestaController', ['index', 'store', 'update']);
    // Route::get('editEncuesta/{id}', 'EncuestaController@edit');

    // // INTEGRACIÓN ENCUESTAS - PRODUCTOS
    // Route::get('getPreguntas/{catalogo}', 'EncuestaController@getPreguntas');
    // Route::get('getValoraciones/{catalogo}', 'EncuestaController@getValoraciones');
    // Route::get('getProductoValoraciones/{producto}', 'EncuestaController@getProductoValoraciones');
    // Route::post('storeRespuestas', 'EncuestaController@storePreguntas');
    // Route::get('eliminarPregunta/{pregunta}', 'EncuestaController@destroyPregunta');

    // // PQRS
    // Route::apiResource('pqrs', 'PqrsController');
    // Route::post('newMessage', 'PqrsController@NewMessage');
    // Route::get('changeState/{id}/{state}', 'PqrsController@changeState');
    // Route::get('getPqrsUser', 'PqrsController@getPqrsUserSesion');
});
