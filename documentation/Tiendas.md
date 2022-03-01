# API - Monitoreo

```js
const apiUrl = "http://montanabackend.test/api";
```

------------------------------------------

##### Obtener tiendas de un cliente

```js
// Request
axios.get(`${apiUrl}/tiendas-cliente/142`);
```

```json
// Respuesta
[
    {
        "id_tiendas": 360,
        "sucursal": "E",
        "nombre": "FYC CALZADO SAS",
        "lugar": "COSMOS VIA AEROPUERTO",
        "local": "",
        "direccion": "KM 3 UN IND LA REGIONAL GLORIETA AEROPUERTO JMC BG",
        "telefono": "4480529",
        "fecha_ingreso": "2020-11-04 00:00:00",
        "fecha_ultima_compra": "2021-08-05 00:00:00",
        "cupo": 0,
        "ciudad_codigo": "615",
        "zona": "05",
        "bloqueado": "N",
        "bloqueado_fecha": null,
        "nombre_representante": "",
        "plazo": 45,
        "escala_factura": "A",
        "observaciones": "SIEMPRE  SE LES MANEJA DESC 5%",
        "cliente": 586,
        "created_at": "2022-02-18 15:01:18",
        "updated_at": "2022-02-18 18:09:56"
    }
]
```

------------------------------------------

##### Obtener detalle de tienda

```js
// Request
axios.get(`${apiUrl}/tiendas-cliente/50`);
```

```json
// Respuesta
{
    "id_tiendas": 50,
    "sucursal": "L",
    "nombre": "EVACOL",
    "lugar": "BOGOTA CALIMA",
    "local": "",
    "direccion": "CC CALIMA LOCAL A-119, CL 19 28-80",
    "telefono": "3786646 EXT 307",
    "fecha_ingreso": "2017-08-23 00:00:00",
    "fecha_ultima_compra": "2017-09-29 00:00:00",
    "cupo": 0,
    "ciudad_codigo": "001",
    "zona": "11",
    "bloqueado": "N",
    "bloqueado_fecha": null,
    "nombre_representante": "",
    "plazo": 60,
    "escala_factura": "A",
    "observaciones": "",
    "cliente": 293,
    "created_at": "2022-02-18 15:01:07",
    "updated_at": "2022-02-18 18:09:40",
    "propietario": {
        "id": 293,
        "rol_id": 3,
        "name": "2MYW SAS",
        "apellidos": "",
        "email": "2myw-sas900452868-1@example.com",
        "tipo_identificacion": "Cedula",
        "dni": "900452868-1",
        "created_at": "2022-02-18 15:01:07",
        "updated_at": "2022-02-18 18:09:40"
    },
    "vendedores": [
        {
            "id": 223,
            "rol_id": 2,
            "name": "VENTAS DIRECTAS",
            "apellidos": "",
            "email": "ventas-directas01@example.com",
            "tipo_identificacion": "Cedula",
            "dni": "",
            "created_at": "2022-02-18 14:58:32",
            "updated_at": "2022-02-18 14:58:32",
            "pivot": {
                "tienda_id": 50,
                "vendedor_id": 223
            }
        }
    ]
}
```

------------------------------------------

##### Crear tienda a un cliente

```js
// Request
// ${apiUrl}/newTienda/cliente_id
axios.post(`${apiUrl}/newTienda/4`, {
    nombre: 'El Cid',
    lugar: 'Palmeto',
    local: 'L 202',
    direccion: 'Calle 8 # 2 - 3',
    telefono: '+573001254875',
    sucursal: 'L',
    fecha_ingreso: '2022-02-12',
    fecha_ultima_compra: '2022-02-12',
    cupo: 0,
    ciudad_codigo: '001',
    zona: '11',
    bloqueado: 'N',
    bloqueado_fecha: null, // yyyy-mm-dd
    nombre_representante: '',
    plazo: 0
    escala_factura: 'A',
    observaciones: 'texto de observación',
    vendedores: [
        3,
        141,
    ]
});
```

```json
// Respuesta
{
    "tienda_id": 1243,
    "response": "success",
    "status": 200
}
```

------------------------------------------

##### Actualizar tienda

```js
// Request
// ${apiUrl}/tiendas/tienda_id
axios.put(`${apiUrl}/tiendas/1243`, {
    nombre: 'El Cid abc',
    lugar: 'Palmeto 123',
    local: 'L 202',
    direccion: 'Calle 8 # 2 - 3',
    telefono: '+573001254875',
    sucursal: 'L',
    fecha_ingreso: '2022-02-12',
    fecha_ultima_compra: '2022-02-12',
    cupo: 0,
    ciudad_codigo: '001',
    zona: '11'
    bloqueado: 'N',
    bloqueado_fecha: null, // yyyy-mm-dd
    nombre_representante: '',
    plazo: 0,
    escala_factura: 'A',
    observaciones: 'texto de observación1',
    vendedores: {
        3,
        141,
        1413,
    }
});
```

```json
// Respuesta
{
    "tienda_id": 1243,
    "response": "success",
    "status": 200
}
```

------------------------------------------

##### Crear multiples tiendas

```js
// Request
axios.post(`${apiUrl}/tiendas`, {
    cliente: 214,
    tiendas: [
        {
            nombre: 'El Cid',
            lugar: 'Palmeto',
            local: 'L 202',
            direccion: 'Calle 8 # 2 - 3',
            telefono: '+573001254875',
            sucursal: 'L',
            fecha_ingreso: '2022-02-12',
            fecha_ultima_compra: '2022-02-12',
            cupo: 0,
            ciudad_codigo: '001',
            zona: '11',
            bloqueado: 'N',
            bloqueado_fecha: null,
            nombre_representante: '',
            plazo: 0,
            escala_factura: 'A',
            observaciones: 'texto de observación 1',
            vendedores: [
                3,
                141,
            ]
        },
        {
            nombre: 'El Paramo',
            lugar: 'Limonar',
            local: 'L 101',
            direccion: 'Carrear 10 # 2 - 3',
            telefono: '+573103214875',
            sucursal: 'K',
            fecha_ingreso: '2021-02-12',
            fecha_ultima_compra: '2021-02-12',
            cupo: 60,
            ciudad_codigo: '002',
            zona: '11',
            bloqueado: 'S',
            bloqueado_fecha: '2022-01-01',
            nombre_representante: '',
            plazo: 0,
            escala_factura: 'A',
            observaciones: 'texto de observación 2',
            vendedores: [
                3,
            ]
        }
    ]

});
```

```json
// Respuesta
{
    "tiendas_ids": [
        1246,
        1247
    ],
    "response": "success",
    "status": 200
}
```

------------------------------------------

##### Eliminar multiples tiendas

```js
// Request
axios.post(`${apiUrl}/delete-tiendas`, {
    tiendas: [
        1239,
        1238,
        1237,
    ]
});
```

```json
// Respuesta
{
    "response": "success",
    "status": 200,
    "message": "Tienda(s) eliminada(s)."
}
```