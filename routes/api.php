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


Route::apiResource('/roles', 'RolController');
// Route::apiResource('/users', 'UserController');
// Route::post('/userdata', 'UserController@userData');

Route::apiResource('/asignar-cliente', 'VendedorClienteController');
Route::get('/unauthenticated', function(){
    $response = [
        'response' => 'error',
        'status' => 403,
        'message' => 'Token vencido o invalido.'
    ];
    return response()->json($response, $response['status']);
})->name('unauthenticated');

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
        Route::get('/getUserSesion','AuthController@getUserSesion');
    });
});

Route::group(['middleware' => 'auth:api'], function() {
    
    /*
        USUARIOS
    */ 
    Route::post('/delete-users', 'UserController@destroyUsers');
    Route::get('/user-rol/{id}','UserController@getForRole');
    // Route::post('/delete-user', 'UserController@destroyUsers');
        
        // ADMINISTRADORES
        Route::get('/admins', 'UserController@getAdmins');
        Route::get('/admin/{id}', 'UserController@getAdmin');
    
        //VENDEDORES 
        Route::get('/vendedores','UserController@getVendedores');
        Route::get('/vendedor/{id}','UserController@getVendedor');
        Route::get('/clientes-asignados/{id}', 'UserController@assignedCustomers');
        Route::get('/searchClientes', 'UserController@searchClientes');
        Route::post('/update-vendedor/{id}', 'UserController@updateVendedor');
        Route::get('updateAsignClient/{cliente}/{vendedor}/{action}', 'UserController@updateAsignClient');
        
        // CLIENTES
        Route::get('/clientes','UserController@getClientes');
        Route::get('/cliente/{id}','UserController@getCliente');
        Route::post('/update-cliente/{id}','UserController@updateClient');
        Route::get('/searchVendedor', 'UserController@searchVendedor');
        Route::get('updateAsignVend/{cliente}/{vendedor}/{action}', 'UserController@updateAsignVend');
        Route::post('newTienda/{cliente}', 'UserController@newTienda');

    // CATALOGOS
    Route::apiResource('/catalogos', 'CatalogoController');
    Route::get('consumerCatalogos', 'CatalogoController@consumerCatalogos');
    
    // PRODUCTO
    Route::get('/productos/{catalogo}', 'ProductoController@index');
    Route::post('/productos', 'ProductoController@store');
    Route::get('/producto/{id}', 'ProductoController@detalleProducto');
    Route::put('/producto/{id}', 'ProductoController@update');
    Route::delete('/producto/{id}', 'ProductoController@destroy');
    
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

    // TIENDAS
    Route::apiResource('tiendas', 'TiendaController', ['store', 'update']);
    route::post('delete-tiendas', 'TiendaController@destroy'); 

    // ENCUESTAS
    Route::apiResource('encuestas', 'EncuestaController', ['index', 'store', 'update']);
    Route::get('editEncuesta/{id}', 'EncuestaController@edit');
        // Integracion encuestas - productos.
        Route::get('getPreguntas/{catalogo}', 'EncuestaController@getPreguntas');
        Route::post('storeRespuestas', 'EncuestaController@storePreguntas');
        Route::get('eliminarPregunta/{pregunta}', 'EncuestaController@destroyPregunta');

    // AMPLIACION CUPO
    Route::apiResource('ampliacion-cupo', 'AmpliacionCupoController', ['index', 'store', 'update']);
    Route::get('getUserSmall/{rol_id}', 'AmpliacionCupoController@getUserSmall');
    Route::get('cambiar-estado/{solicitud}/{estado}', 'AmpliacionCupoController@changeState');

    // PQRS
    Route::apiResource('pqrs', 'PqrsController');
    Route::post('newMessage', 'PqrsController@NewMessage');
    Route::get('changeState/{id}/{state}', 'PqrsController@changeState');

    // DESCUENTOS
    Route::get('getPedidoWithCode/{code}', 'PedidoController@getPedidoWithCode');
    Route::get('changeDescuentoPedido/{pedido}/{descuento}', 'PedidoController@changeDescuentoPedido');

    // SHOW ROOM
    Route::get('getProductsShowRoom', 'ProductoController@getProductsShowRoom');

});