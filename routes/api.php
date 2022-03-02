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
    Route::middleware(['rol:administrador'])->get('monitoreo', 'MonitoreoController@index');
});

Route::group(['namespace' => 'Auth'], function () {
    Route::post('password/email', 'PasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'PasswordController@reset');
});

// AUTH
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

    // OTROS
    Route::post('devices', 'DevicesController@post')->name('devices.store');
    Route::get('/dashboard-resumen', 'ResumenController@dashboardResumen')->name('resume.show');

    // USUARIOS
    Route::middleware(['rol:administrador|vendedor'])->get('/users', 'UserController@index')->name('users.index');
    Route::middleware(['rol:administrador|vendedor'])->post('/users', 'UserController@store')->name('users.store');
    Route::middleware(['rol:administrador|vendedor'])->post('/update-user/{id}', 'UserController@actualizarUsuario')->name('users.update');
    Route::middleware(['rol:administrador|vendedor'])->post('/delete-users', 'UserController@eliminarUsuarios')->name('users.destroy');
    Route::middleware(['rol:administrador|vendedor'])->get('/user-rol/{rol_id}', 'UserController@usuariosPorRol')->name('roles.users.index');
    Route::middleware(['rol:administrador|vendedor'])->get('/roles', 'UserController@roles')->name('roles.index');

    // ADMINISTRADORES
    Route::middleware(['rol:administrador'])->get('/admins', 'UserController@administradores')->name('admins.index');
    Route::middleware(['rol:administrador'])->get('/admin/{id}', 'UserController@administrador')->name('admins.show');

    // VENDEDORES
    Route::get('/vendedores', 'UserController@vendedores')->name('sellers.index');
    Route::get('/vendedor/{id}', 'UserController@vendedor')->name('sellers.show');
    Route::get('/searchClientes', 'UserController@buscarClientes')->name('sellers.clients.search');
    Route::get('/clientes-asignados/{vendedor_id}', 'UserController@clientesAsignados')->name('sellers.clients.index');
    Route::middleware(['rol:administrador'])->post('vendedores/{vendedor_id}/tiendas/{tienda_id}/asignar', 'UserController@asignarVendedorTienda')->name('sellers.attach');
    Route::middleware(['rol:administrador'])->post('vendedores/{vendedor_id}/tiendas/{tienda_id}/quitar', 'UserController@quitarVendedorTienda')->name('sellers.detach');

    // CLIENTES
    Route::get('/clientes', 'UserController@clientes')->name('clients.index');
    Route::get('/cliente/{id}', 'UserController@cliente')->name('clients.show');
    Route::get('/searchVendedor', 'UserController@buscarVendedor')->name('clients.sellers.search');
    Route::get('/vendedores-asignados/{cliente_id}', 'UserController@vendedoresAsignados')->name('clients.sellers.index');

    // CATALOGOS
    Route::apiResource('/catalogos', 'CatalogoController', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    Route::get('consumerCatalogos', 'CatalogoController@catalogosActivos');

    // PRODUCTO
    Route::get('/marcas', 'ProductoController@marcas');
    Route::get('getProductsShowRoom', 'ProductoController@productosShowRoom');
    Route::post('/productos', 'ProductoController@store');
    Route::get('/productos/{catalogo}', 'ProductoController@index');
    Route::get('/producto/{producto}', 'ProductoController@show');
    Route::match(['put', 'patch'], '/producto/{producto}', 'ProductoController@update');
    Route::delete('/producto/{producto}', 'ProductoController@destroy');

    // PEDIDOS
    Route::apiResource('/pedidos', 'PedidoController', ['only' => ['index', 'show', 'store', 'update']]);
    Route::get('/recursos-crear-pedido', 'PedidoController@recursosCrearPedido');
    Route::get('generate-code', 'PedidoController@generarCodigoPedido');
    Route::post('change-state-pedido', 'PedidoController@cambiarEstadoPedido');
    Route::post('crear-novedad', 'PedidoController@crearNovedad');
    Route::get('getPedidoWithCode/{codigo}', 'PedidoController@pedidoPorCodigo');
    Route::post('changeDescuentoPedido/{pedido}/{descuento}', 'PedidoController@cambiarDescuentoPedido');
    Route::get('export-pedido', 'PedidoController@exportPedido');

    // TIENDAS
    Route::apiResource('tiendas', 'TiendaController', ['only' => ['store', 'update', 'show']]);
    Route::get('tiendas-cliente/{cliente}', 'TiendaController@clienteTiendas');
    Route::post('newTienda/{cliente}', 'TiendaController@nuevaTienda');
    Route::post('delete-tiendas', 'TiendaController@eliminarTiendas');

    // AMPLIACION CUPO
    Route::apiResource('ampliacion-cupo', 'AmpliacionCupoController', ['only' => ['index', 'store', 'update']]);
    Route::get('getUserSmall/{rol_id}', 'AmpliacionCupoController@usuariosPorRol');
    Route::post('cambiar-estado/{solicitud}/{estado}', 'AmpliacionCupoController@changeState');

    // IMPORTAR DB
    Route::middleware(['rol:administrador'])->post('batch/importar-marcas', 'BatchDataController@importarMarcas');
    Route::middleware(['rol:administrador'])->post('batch/importar-productos', 'BatchDataController@importarProductos');
    Route::middleware(['rol:administrador'])->post('batch/importar-vendedores', 'BatchDataController@importarVendedores');
    Route::middleware(['rol:administrador'])->post('batch/importar-clientes', 'BatchDataController@importarClientes');
    Route::middleware(['rol:administrador'])->post('batch/importar-cartera', 'BatchDataController@importarCartera');

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
