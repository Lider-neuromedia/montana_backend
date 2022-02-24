# API - Usuarios Clientes

```js
const apiUrl = "http://montanabackend.test/api";
```

------------------------------------------

##### Obtener clientes (puede filtrar con search)

```js
// Request
axios.get(`${apiUrl}/clientes?page=50&search=juan`);
```

```json
// Respuesta
{
    "fields": [
        "nit",
        "razon_social",
        "direccion",
        "telefono",
        "codigo"
    ],
    "users": {
        "current_page": 50,
        "data": [
            {
                "id": 777,
                "rol_id": 3,
                "name": "LANCHEROS AMAYA VLADIMIR",
                "apellidos": "",
                "email": "lancheros-amaya-vladimir79904763-9@example.com",
                "tipo_identificacion": "Cedula",
                "dni": "79904763-9",
                "created_at": "2022-02-18 15:01:25",
                "updated_at": "2022-02-18 18:10:19",
                "datos": [
                    {
                        "id": 64128,
                        "user_id": 777,
                        "field_key": "nit",
                        "value_key": "79904763-9"
                    },
                    {
                        "id": 64129,
                        "user_id": 777,
                        "field_key": "razon_social",
                        "value_key": "LANCHEROS AMAYA VLADIMIR"
                    },
                    {
                        "id": 64130,
                        "user_id": 777,
                        "field_key": "telefono",
                        "value_key": "3747970 3192565714"
                    },
                    {
                        "id": 64131,
                        "user_id": 777,
                        "field_key": "direccion",
                        "value_key": "CR 24 63 C 62"
                    }
                ]
            }
        ],
        "first_page_url": "http://montanabackend.test//api/clientes?page=1",
        "from": 491,
        "last_page": 113,
        "last_page_url": "http://montanabackend.test//api/clientes?page=113",
        "next_page_url": "http://montanabackend.test//api/clientes?page=51",
        "path": "http://montanabackend.test//api/clientes",
        "per_page": 10,
        "prev_page_url": "http://montanabackend.test//api/clientes?page=49",
        "to": 500,
        "total": 1125
    }
}
```

------------------------------------------

##### Buscar clientes

```js
// Request
axios.get(`${apiUrl}/searchClientes?search=juan&page=1`);
```

```json
// Respuesta
{
    "response": "success",
    "clientes": {
        "current_page": 1,
        "data": [
            {
                "id": 4,
                "rol_id": 3,
                "name": "Juan Jose",
                "apellidos": "Borrero",
                "email": "emanuel@gmail.com",
                "tipo_identificacion": "Cedula",
                "dni": "42424425",
                "created_at": "2020-07-30 07:18:47",
                "updated_at": "2021-01-25 08:20:40"
            },
        ],
        "first_page_url": "http://montanabackend.test//api/searchClientes?page=1",
        "from": 1,
        "last_page": 4,
        "last_page_url": "http://montanabackend.test//api/searchClientes?page=4",
        "next_page_url": "http://montanabackend.test//api/searchClientes?page=2",
        "path": "http://montanabackend.test//api/searchClientes",
        "per_page": 10,
        "prev_page_url": null,
        "to": 10,
        "total": 33
    },
    "status": 200
}
```
------------------------------------------

##### Obtener usuario cliente

```js
// Request
axios.get(`${apiUrl}/cliente/142`);
```

```json
// Respuesta
{
    "id": 142,
    "rol_id": 3,
    "name": "John",
    "apellidos": "Doe",
    "email": "cliente1@gmail.com",
    "tipo_identificacion": "Cedula",
    "dni": "4684654",
    "created_at": "2021-02-24 03:52:16",
    "updated_at": "2021-02-24 03:52:16",
    "datos": [
        {
            "id": 71,
            "user_id": 142,
            "field_key": "nit",
            "value_key": "123"
        },
        {
            "id": 72,
            "user_id": 142,
            "field_key": "razon_social",
            "value_key": "123"
        },
        {
            "id": 73,
            "user_id": 142,
            "field_key": "direccion",
            "value_key": "asdasd"
        },
        {
            "id": 74,
            "user_id": 142,
            "field_key": "telefono",
            "value_key": "123"
        }
    ]
}
```

------------------------------------------

##### Obtener vendedores asignados a cliente

```js
// Request
axios.get(`${apiUrl}/vendedores-asignados/142`);
```

```json
// Respuesta
[
    {
        "id": 3,
        "rol_id": 2,
        "name": "John",
        "apellidos": "Doe",
        "email": "john@mail.com",
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
    }
]
```
