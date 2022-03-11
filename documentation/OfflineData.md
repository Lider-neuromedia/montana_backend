# API - Offline Data

```js
const apiUrl = "http://montanabackend.test/api";
```

------------------------------------------

##### Obtener catálogos

```js
// Request
axios.get(`${apiUrl}/offline/catalogos`);
```

```json
// Respuesta
[
    {
        "id_catalogo": 3,
        "estado": "activo",
        "tipo": "show room",
        "imagen": "http://montanabackend.test/storage/catalogos/3.png",
        "titulo": "Primavera 2021",
        "cantidad": 2738,
        "descuento": 5,
        "etiqueta": "adultos",
        "created_at": "2020-09-11 21:00:33",
        "updated_at": "2022-02-25 17:59:49"
    }
]
```

------------------------------------------

##### Obtener clientes

```js
// Request
axios.get(`${apiUrl}/offline/clientes`);
```

```json
// Respuesta
[
    {
        "id": 4,
        "rol_id": 3,
        "name": "Juan Jose",
        "apellidos": "Borrero",
        "email": "emanuel@gmail.com",
        "tipo_identificacion": "Cedula",
        "dni": "42424425",
        "created_at": "2020-07-30 07:18:47",
        "updated_at": "2022-03-02 16:59:54",
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
    }
]
```

------------------------------------------

##### Obtener tiendas

```js
// Request
axios.get(`${apiUrl}/offline/tiendas`);
```

```json
// Respuesta
[
    {
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
        "cliente": {
            "id": 4,
            "rol_id": 3,
            "name": "Juan Jose",
            "apellidos": "Borrero",
            "email": "emanuel@gmail.com",
            "tipo_identificacion": "Cedula",
            "dni": "42424425",
            "created_at": "2020-07-30 07:18:47",
            "updated_at": "2022-03-02 16:59:54"
        },
        "created_at": "2020-10-30 05:42:35",
        "updated_at": "2021-03-30 22:03:24",
        "cliente_id": 4,
        "vendedores": [
            {
                "id": 1413,
                "rol_id": 2,
                "name": "John",
                "apellidos": "Doe",
                "email": "johndoe2@mail.com",
                "tipo_identificacion": "Cedula",
                "dni": "613481684",
                "created_at": "2022-02-23 15:00:09",
                "updated_at": "2022-02-23 16:22:27",
                "pivot": {
                    "tienda_id": 1,
                    "vendedor_id": 1413
                }
            }
        ]
    }
]
```

------------------------------------------

##### Obtener pedidos

```js
// Request
axios.get(`${apiUrl}/offline/pedidos`);
```

```json
// Respuesta
[
    {
        "id_pedido": 36,
        "fecha": "2021-04-08",
        "codigo": "606f37e61b0c8",
        "metodo_pago": "contado",
        "sub_total": 3600000,
        "total": 3420000,
        "notas": "",
        "notas_facturacion": "",
        "firma": "http://montanabackend.test/storage/firmas/ZARYLDF6aUlkbKOEVyyQrSkylUx02Oh2YI7tWh0C.png",
        "vendedor": {
            "id": 196,
            "rol_id": 2,
            "name": "Nelson",
            "apellidos": "Zambrano",
            "email": "nelsonzambranojr23@gmail.com",
            "tipo_identificacion": "Cedula",
            "dni": "1107",
            "created_at": "2021-03-30 23:53:54",
            "updated_at": "2021-04-07 01:13:14",
            "datos": [
                {
                    "id": 161,
                    "user_id": 196,
                    "field_key": "telefono",
                    "value_key": "123456"
                },
                {
                    "id": 162,
                    "user_id": 196,
                    "field_key": "codigo",
                    "value_key": "123"
                }
            ]
        },
        "cliente": {
            "id": 197,
            "rol_id": 3,
            "name": "Diego",
            "apellidos": "Ramirez",
            "email": "diegolazo@gmail.com",
            "tipo_identificacion": "Cedula",
            "dni": "1107444555",
            "created_at": "2021-03-30 23:55:27",
            "updated_at": "2021-03-30 23:55:27",
            "datos": [
                {
                    "id": 163,
                    "user_id": 197,
                    "field_key": "nit",
                    "value_key": "123"
                },
                {
                    "id": 164,
                    "user_id": 197,
                    "field_key": "razon_social",
                    "value_key": "DRLazo"
                },
                {
                    "id": 165,
                    "user_id": 197,
                    "field_key": "direccion",
                    "value_key": "calle 56"
                }
            ]
        },
        "estado": {
            "id": 2,
            "estado": "pendiente"
        },
        "estado_id": 2,
        "vendedor_id": 196,
        "cliente_id": 197,
        "detalles": [
            {
                "producto": {
                    "id_producto": 26,
                    "nombre": "Producto 1",
                    "codigo": "001",
                    "referencia": "001",
                    "stock": 88,
                    "precio": 300000,
                    "descripcion": "Hola esta prueba",
                    "sku": "",
                    "total": 300000,
                    "descuento": 0,
                    "iva": 19,
                    "created_at": "2021-03-30 04:11:58",
                    "updated_at": "2021-04-08 22:06:50",
                    "catalogo_id": 0,
                    "marca_id": 0
                },
                "cantidad_producto": 7,
                "tienda": {
                    "id_tiendas": 33,
                    "sucursal": "",
                    "nombre": "DiegoLazo",
                    "lugar": "Cali",
                    "local": "123",
                    "direccion": "calle 56d",
                    "telefono": "calle 56d",
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
                    "created_at": "2021-03-30 18:55:27",
                    "updated_at": "2021-03-30 18:55:27",
                    "cliente_id": 197
                },
                "referencia": "001",
                "lugar": "Cali",
                "id": 57,
                "pedido_id": 36,
                "producto_id": 26,
                "tienda_id": 33
            },
            {
                "producto": {
                    "id_producto": 26,
                    "nombre": "Producto 1",
                    "codigo": "001",
                    "referencia": "001",
                    "stock": 88,
                    "precio": 300000,
                    "descripcion": "Hola esta prueba",
                    "sku": null,
                    "total": 300000,
                    "descuento": 0,
                    "iva": 19,
                    "created_at": "2021-03-30 04:11:58",
                    "updated_at": "2021-04-08 22:06:50",
                    "catalogo_id": 0,
                    "marca_id": 0
                },
                "cantidad_producto": 5,
                "tienda": {
                    "id_tiendas": 44,
                    "sucursal": "",
                    "nombre": "zambrano benavides",
                    "lugar": "cali",
                    "local": "123",
                    "direccion": "calle 34",
                    "telefono": "calle 34",
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
                    "created_at": "2021-04-08 13:32:54",
                    "updated_at": "2021-04-08 13:32:54",
                    "cliente_id": 197
                },
                "referencia": "001",
                "lugar": "cali",
                "id": 58,
                "pedido_id": 36,
                "producto_id": 26,
                "tienda_id": 44
            }
        ],
        "novedades": [
            {
                "id_novedad": 30,
                "tipo": "retraso en envío",
                "descripcion": "Estamos despachando el pedido",
                "pedido": 36,
                "created_at": "2021-04-08 22:08:31",
                "updated_at": "2021-04-08 22:08:31"
            }
        ]
    }
]
```

------------------------------------------

##### Obtener productos

```js
// Request
axios.get(`${apiUrl}/offline/productos`);
```

```json
// Respuesta
[
    {
        "id_producto": 22,
        "nombre": "ATHLETIC AIR REF.JJSA-1613L-05 GRIS/SALMON PARROT",
        "codigo": "01010386",
        "referencia": "JJSA-1613L-05",
        "stock": 60,
        "precio": 35000,
        "descripcion": "",
        "sku": "",
        "total": 35000,
        "descuento": 0,
        "iva": 0,
        "catalogo": 3,
        "marca": {
            "id_marca": 1,
            "nombre_marca": "ATHLETIC",
            "codigo": "01"
        },
        "created_at": "2020-10-27 08:08:49",
        "updated_at": "2022-03-02 15:32:12",
        "marca_id": 1,
        "image": "http://montanabackend.test/storage/productos/3/ATH-30303/ATH-30303-0.png",
        "imagenes": [
            {
                "image": "http://montanabackend.test/storage/productos/3/ATH-30303/ATH-30303-0.png",
                "name_img": "ATH-30303-0",
                "destacada": 1,
                "producto": 22,
                "id": 24
            },
            {
                "image": "http://montanabackend.test/storage/productos/3/ATH-30303/ATH-30303-1.png",
                "name_img": "ATH-30303-1",
                "destacada": 0,
                "producto": 22,
                "id": 25
            },
            {
                "image": "http://montanabackend.test/storage/productos/3/ATH-30303/ATH-30303-2.png",
                "name_img": "ATH-30303-2",
                "destacada": 0,
                "producto": 22,
                "id": 29
            }
        ]
    },
]
```

------------------------------------------

##### Obtener imagenes de productos y catálogos

```js
// Request
axios.get(`${apiUrl}/offline/imagenes`);
```

```json
// Respuesta
[
    "http://montanabackend.test/storage/productos/3/ATH-30303/ATH-30303-0.png",
    "http://montanabackend.test/storage/productos/57/001/001-1.png",
    "http://montanabackend.test/storage/catalogos/15.png",
    "http://montanabackend.test/storage/catalogos/16.png",
    "http://montanabackend.test/storage/catalogos/66.png"
]
```