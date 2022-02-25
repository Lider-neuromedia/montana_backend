# API - Usuarios Administradores

```js
const apiUrl = "http://montanabackend.test/api";
```

------------------------------------------

##### Obtener administradores (puede filtrar con search)

```js
// Request
axios.get(`${apiUrl}/admins?page=1&search=juan`);
```

```json
// Respuesta
{
    "fields": [
        "telefono"
    ],
    "users": {
        "current_page": 1,
        "data": [
            {
                "id": 145,
                "rol_id": 1,
                "name": "juan",
                "apellidos": "salazar",
                "email": "juan@mail.com",
                "tipo_identificacion": "Cedula",
                "dni": "123",
                "created_at": "2021-02-25 22:20:09",
                "updated_at": "2021-02-25 22:20:09"
            }
        ],
        "first_page_url": "http://montanabackend.test//api/admins?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://montanabackend.test//api/admins?page=1",
        "next_page_url": null,
        "path": "http://montanabackend.test//api/admins",
        "per_page": 10,
        "prev_page_url": null,
        "to": 1,
        "total": 1
    }
}
```

------------------------------------------

##### Obtener detalle de administrador

```js
// Request
axios.get(`${apiUrl}/admins/145`);
```

```json
// Respuesta
{
    "id": 145,
    "rol_id": 1,
    "name": "juan",
    "apellidos": "salazar",
    "email": "juan@mail.com",
    "tipo_identificacion": "Cedula",
    "dni": "123",
    "created_at": "2021-02-25 22:20:09",
    "updated_at": "2021-02-25 22:20:09",
    "datos": [
        {
            "id": 78,
            "user_id": 145,
            "field_key": "telefono",
            "value_key": "3216149165"
        }
    ]
}
```
