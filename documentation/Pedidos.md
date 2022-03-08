# API - Pedidos

```js
const apiUrl = "http://montanabackend.test/api";
```

------------------------------------------

##### Obtener pedidos

```js
// Request
axios.get(`${apiUrl}/pedidos?page=1&search=7000`);
```

```json
// Respuesta
{
    "pedidos": {
        "current_page": 1,
        "data": [
            {
                "id_pedido": 38,
                "fecha": "2021-04-12",
                "codigo": "607457f9d7000",
                "total": 300000,
                "firma": "http://montanabackend.test/storage/firmas/ZARYLDF6aUlkbKOEVyyQrSkylUx02Oh2YI7tWh0C.png",
                "vendedor": {
                    "id_vendedor": 3,
                    "nombre": "Carlos Duque"
                },
                "cliente": {
                    "id_cliente": 133,
                    "nombre": "Daniel Martinez"
                },
                "estado": {
                    "id_estado": 2,
                    "estado": "pendiente"
                }
            }
        ],
        "first_page_url": "http://montanabackend.test//api/pedidos?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://montanabackend.test//api/pedidos?page=1",
        "next_page_url": null,
        "path": "http://montanabackend.test//api/pedidos",
        "per_page": 20,
        "prev_page_url": null,
        "to": 2,
        "total": 2
    },
    "response": "success",
    "status": 200
}
```

------------------------------------------

##### Obtener detalle de pedido

```js
// Request
axios.get(`${apiUrl}/pedidos/23`);
```

```json
// Respuesta
{
    "status": 200,
    "response": "success",
    "pedido": {
        "id_pedido": 23,
        "fecha": "2020-10-27",
        "codigo": "5f9876ea33765",
        "metodo_pago": "contado",
        "sub_total": 1650000,
        "total": 1171500,
        "notas": "Ninguna",
        "notas_facturacion": null,
        "firma": "http://montanabackend.test/storage/firmas/ZARYLDF6aUlkbKOEVyyQrSkylUx02Oh2YI7tWh0C.png",
        "vendedor": {
            "id": 3,
            "rol_id": 2,
            "name": "Carlos",
            "apellidos": "Duque",
            "email": "carlos@gmail.com",
            "tipo_identificacion": "Cedula",
            "dni": "452524",
            "created_at": "2020-07-30 07:18:25",
            "updated_at": "2021-03-30 19:44:30",
            "datos": [
                {
                    "id": 38,
                    "user_id": 3,
                    "field_key": "telefono",
                    "value_key": "321656127"
                },
                {
                    "id": 39,
                    "user_id": 3,
                    "field_key": "codigo",
                    "value_key": "1"
                }
            ]
        },
        "cliente": {
            "id": 4,
            "rol_id": 3,
            "name": "Juan Jose",
            "apellidos": "Borrero",
            "email": "emanuel@gmail.com",
            "tipo_identificacion": "Cedula",
            "dni": "42424425",
            "created_at": "2020-07-30 07:18:47",
            "updated_at": "2021-01-25 08:20:40",
            "datos": [
                {
                    "id": 46,
                    "user_id": 4,
                    "field_key": "nit",
                    "value_key": "6516516"
                },
                {
                    "id": 51,
                    "user_id": 4,
                    "field_key": "razon_social",
                    "value_key": "Zapatillas zoe"
                },
                {
                    "id": 52,
                    "user_id": 4,
                    "field_key": "direccion",
                    "value_key": "Cll 33 #12-12"
                },
                {
                    "id": 53,
                    "user_id": 4,
                    "field_key": "telefono",
                    "value_key": "9393334"
                }
            ]
        },
        "estado": {
            "id_estado": 3,
            "estado": "cancelado"
        },
        "estado_id": 3,
        "vendedor_id": 3,
        "cliente_id": 4,
        "detalles": [
            {
                "producto": {
                    "id_producto": 22,
                    "nombre": "ATHLETIC AIR REF.JJSA-1613L-05 GRIS/SALMON PARROT",
                    "codigo": "01010386",
                    "referencia": "JJSA-1613L-05",
                    "stock": 0,
                    "precio": 35000,
                    "descripcion": "",
                    "sku": "",
                    "total": 35000,
                    "descuento": 0,
                    "iva": 0,
                    "catalogo_id": 3,
                    "marca_id": 1,
                    "created_at": "2020-10-27 08:08:49",
                    "updated_at": "2022-02-18 14:57:30"
                },
                "cantidad_producto": 3,
                "tienda": {
                    "id_tiendas": 1,
                    "sucursal": "",
                    "nombre": "Atletic norte",
                    "lugar": "unico 2",
                    "local": "202",
                    "direccion": "cll 52",
                    "telefono": "30303",
                    "fecha_ingreso": null,
                    "fecha_ultima_compra": null,
                    "cupo": 0,
                    "ciudad_codigo": "",
                    "zona": "",
                    "bloqueado": "",
                    "bloqueado_fecha": null,
                    "nombre_representante": "",
                    "plazo": 0,
                    "escala_factura": "",
                    "observaciones": null,
                    "cliente_id": 4,
                    "created_at": "2020-10-30 05:42:35",
                    "updated_at": "2021-03-30 22:03:24"
                },
                "referencia": "JJSA-1613L-05",
                "lugar": "unico 2",
                "id": 27,
                "pedido_id": 23,
                "tienda_id": 1,
                "producto_id": 22
            },
            {
                "producto": {
                    "id_producto": 22,
                    "nombre": "ATHLETIC AIR REF.JJSA-1613L-05 GRIS/SALMON PARROT",
                    "codigo": "01010386",
                    "referencia": "JJSA-1613L-05",
                    "stock": 0,
                    "precio": 35000,
                    "descripcion": "Tenis Puma Adulto Hombre",
                    "sku": "505041",
                    "total": 35000,
                    "descuento": 0,
                    "iva": 0,
                    "catalogo_id": 3,
                    "marca_id": 1,
                    "created_at": "2020-10-27 08:08:49",
                    "updated_at": "2022-02-18 14:57:30"
                },
                "tienda": {
                    "id_tiendas": 2,
                    "sucursal": "",
                    "nombre": "athletic sur",
                    "lugar": "Unicentro",
                    "local": "500",
                    "direccion": "cll 104",
                    "telefono": "6516126",
                    "fecha_ingreso": "2022-01-01",
                    "fecha_ultima_compra": "2022-01-01",
                    "cupo": 0,
                    "ciudad_codigo": "",
                    "zona": "",
                    "bloqueado": "",
                    "bloqueado_fecha": null, // yyyy-mm-dd
                    "nombre_representante": "",
                    "plazo": 0,
                    "escala_factura": "",
                    "observaciones": "",
                    "cliente_id": 4,
                    "created_at": "2020-10-30 05:42:35",
                    "updated_at": "2020-10-30 05:42:35"
                },
                "cantidad_producto": 5,
                "referencia": "JJSA-1613L-05",
                "lugar": "Unicentro",
                "id": 28,
                "pedido_id": 23,
                "tienda_id": 2,
                "producto_id": 22
            }
        ],
        "novedades": [
            {
                "id_novedad": 6,
                "tipo": "retraso en envio",
                "descripcion": "Hubo retraso",
                "pedido": 23,
                "created_at": "2020-10-29 04:17:58",
                "updated_at": "2020-10-29 04:17:58"
            }
        ]
    }
}
```

------------------------------------------

##### Obtener nuevo código de pedido

```js
// Request
axios.get(`${apiUrl}/generate-code`);
```

```json
// Respuesta
{
    "code": "621f8c387b0fc",
    "response": "success",
    "status": 200
}
```

------------------------------------------

##### Obtener recursos para crear/editar pedidos

```js
// Request
axios.get(`${apiUrl}/`);
```

```json
// Respuesta
{
    "vendedores": [
        {
            "id": 237,
            "nombre": "RUBEN DARIO PIEDRAHITA"
        }
    ],
    "clientes": [
        {
            "id": 4,
            "nombre": "Juan Jose Borrero"
        }
    ],
    "catalogos": [
        {
            "id_catalogo": 3,
            "estado": "activo",
            "tipo": "show room",
            "imagen": "http://montanabackend.test/storage/catalogos/31.png",
            "titulo": "Primavera 2021",
            "cantidad": 2738,
            "descuento": 5,
            "etiqueta": "adultos",
            "created_at": "2020-09-11 21:00:33",
            "updated_at": "2022-02-25 17:59:49"
        }
    ],
    "response": "success",
    "status": 200
}
```

------------------------------------------

##### Cambiar estado de pedido

```js
// Request
axios.post(`${apiUrl}/change-state-pedido`, {
    pedido: 23,
    state: 1, // 1. entregado, 2. pendiente, 3. cancelado
});
```

```json
// Respuesta
{
    "status": 200,
    "response": "success",
    "message": "Actualizado."
}
```

------------------------------------------

##### Crear Novedad

```js
// Request
axios.post(`${apiUrl}/crear-novedad`, {
    tipo: "retraso en envio",
    descripcion: "Nueva novedad de prueba 123",
    pedido: 23,
});
```

```json
// Respuesta
{
    "status": 200,
    "response": "success",
    "message": "Novedad creada."
}
```

------------------------------------------

##### Cambiar descuento de pedido

```js
// Request
axios.post(`${apiUrl}/changeDescuentoPedido/23/12`, {}); // pedido_id / descuento
```

```json
// Respuesta
{
    "message": "Descuento actualizado",
    "response": "success",
    "status": 200
}
```

------------------------------------------

##### Exportar pedidos en archivo xlsx

```js
// Request
axios.get(`${apiUrl}/export-pedido`);
```

```json
// Respuesta
pedidos.xlsx
```

------------------------------------------

##### Crear pedido

```js
// Request
axios.post(`${apiUrl}/pedidos`, {
    codigo_pedido: 'f392hy3082yr',
    cliente: 1267,
    vendedor: 141,
    descuento: 0,
    metodo_pago: 'contado',
    total: 5000000,
    notas: 'Nota de descuentos',
    notas_facturacion: 'Nota de facturación',
    firma: 'archivo.png', // Archivo de firma, Max: 2mb
    productos: [
        {
            producto_id: 22,
            tiendas: [
                {
                    id_tienda: 1075,
                    cantidad_producto: 20
                },
                {
                    id_tienda: 2076,
                    cantidad_producto: 10
                }
            ]
        },
        {
            producto_id: 33,
            tiendas: [
                {
                    id_tienda: 1075,
                    cantidad_producto: 8
                }
            ]
        },
        {
            producto_id: 30,
            tiendas: [
                {
                    id_tienda: 1075,
                    cantidad_producto: 5
                }
            ]
        }
    ]
});
```

```json
// Respuesta
{
    "status": 200,
    "response": "success",
    "message": "Pedido guardado correctamente.",
    "pedido_id": 50
}
```

------------------------------------------

##### Actualizar pedido

```js
// Request
axios.put(`${apiUrl}/pedidos/23`, {
    cliente: 1267,
    vendedor: 141,
    descuento: 0,
    metodo_pago: 'contado',
    total: 5000000,
    notas: 'Nota de descuentos',
    notas_facturacion: 'Nota de facturación',
    firma: 'archivo.png', // Archivo de firma, Max: 2mb
    productos: [
        {
            producto_id: 22,
            tiendas: {
                id_tienda: 1075
                cantidad_producto: 20,
            }
        },
        {
            producto_id: 33,
            tiendas: {
                cantidad_producto: 8,
                id_tienda: 1075
            }
        },
        {
            producto_id: 30,
            tiendas: {
                cantidad_producto: 5,
                id_tienda: 1075
            }
        }
    ]
});
```

```json
// Respuesta
{
    "status": 200,
    "response": "success",
    "message": "Pedido guardado correctamente.",
    "pedido_id": 50
}
```