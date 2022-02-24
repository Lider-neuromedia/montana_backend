# API - Usuarios Vendedores

```js
const apiUrl = "http://montanabackend.test/api";
```

------------------------------------------

##### Obtener vendedores

```js
// Request
axios.get(`${apiUrl}/vendedores?search=julian&page=1`);
```

```json
// Respuesta
{
    "fields": [
        "telefono",
        "codigo"
    ],
    "users": {
        "current_page": 1,
        "data": [
            {
                "id": 227,
                "rol_id": 2,
                "name": "JULIAN LLANOS",
                "apellidos": "",
                "email": "julian-llanos38@example.com",
                "tipo_identificacion": "Cedula",
                "dni": "",
                "created_at": "2022-02-18 14:58:33",
                "updated_at": "2022-02-18 14:58:33",
                "datos": [
                    {
                        "id": 230,
                        "user_id": 227,
                        "field_key": "codigo",
                        "value_key": "38"
                    },
                    {
                        "id": 231,
                        "user_id": 227,
                        "field_key": "telefono",
                        "value_key": ""
                    }
                ]
            }
        ],
        "first_page_url": "http://montanabackend.test//api/vendedores?page=1",
        "from": 1,
        "last_page": 8,
        "last_page_url": "http://montanabackend.test//api/vendedores?page=8",
        "next_page_url": "http://montanabackend.test//api/vendedores?page=2",
        "path": "http://montanabackend.test//api/vendedores",
        "per_page": 10,
        "prev_page_url": null,
        "to": 10,
        "total": 73
    }
}
```

------------------------------------------

##### Buscar vendedores
```js
// Request
axios.get(`${apiUrl}/searchVendedor?search=Juan&page=1`);
```

```json
// Respuesta
{
    "response": "success",
    "vendedores": {
        "current_page": 1,
        "data": [
            {
                "id": 3,
                "rol_id": 2,
                "name": "Carlos",
                "apellidos": "Duque",
                "email": "carlos@gmail.com",
                "tipo_identificacion": "Cedula",
                "dni": "452524",
                "created_at": "2020-07-30 07:18:25",
                "updated_at": "2021-03-30 19:44:30"
            },
        ],
        "first_page_url": "http://montanabackend.test//api/searchVendedor?page=1",
        "from": 1,
        "last_page": 8,
        "last_page_url": "http://montanabackend.test//api/searchVendedor?page=8",
        "next_page_url": "http://montanabackend.test//api/searchVendedor?page=2",
        "path": "http://montanabackend.test//api/searchVendedor",
        "per_page": 10,
        "prev_page_url": null,
        "to": 10,
        "total": 73
    },
    "status": 200
}
```

------------------------------------------

##### Obtener detalle de vendedor

```js
// Request
axios.get(`${apiUrl}/vendedor/3`);
```

```json
// Respuesta
{
    "id": 3,
    "rol_id": 2,
    "name": "John",
    "apellidos": "Doe",
    "email": "doe@gmail.com",
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
    ],
}
```

------------------------------------------

##### Obtener clientes asignados a vendedor

```js
// Request
axios.get(`${apiUrl}/clientes-asignados/3`);
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
    }
]
```

------------------------------------------

##### Asignar vendedor a tienda

```js
// Request
axios.post(`${apiUrl}/vendedores/3/tiendas/50/asignar`, {});
```

```json
// Respuesta
{
    "response": "success",
    "message": "Cliente/Tienda asignado a vendedor correctamente.",
    "status": 200
}
```

------------------------------------------

##### Quitar vendedor de tienda

```js
// Request
axios.post(`${apiUrl}/vendedores/3/tiendas/50/quitar`, {});
```

```json
// Respuesta
{
    "response": "success",
    "message": "Cliente/Tienda retirado de vendedor correctamente.",
    "status": 200
}
```

------------------------------------------

#####

```js
// Request
```

```json
// Respuesta
```