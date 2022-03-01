# API - Ampliación de cúpo

```js
const apiUrl = "http://montanabackend.test/api";
```

------------------------------------------

##### Obtener cupos (se puede filtrar con el parametro search)

```js
// Request
axios.get(`${apiUrl}/ampliacion-cupo?page=1&search=Miguel`);
```

```json
// Respuesta
{
    "response": "success",
    "status": 200,
    "solicitudes": {
        "current_page": 1,
        "data": [
            {
                "id_cupo": 7,
                "codigo_solicitud": "5fc7ea8d4d9ad",
                "fecha_solicitud": "2020-12-02",
                "vendedor": {
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
                "cliente": {
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
                "doc_identidad": "http://montanabackend.test/storage/solicitudes/4/doc_identidad.png",
                "doc_rut": "http://montanabackend.test/storage/solicitudes/4/doc_rut.png",
                "doc_camara_com": "http://montanabackend.test/storage/solicitudes/4/doc_camara_comercio.png",
                "monto": 250000,
                "estado": "aceptado"
            }
        ],
        "first_page_url": "http://montanabackend.test//api/ampliacion-cupo?page=1",
        "from": 1,
        "last_page": 2,
        "last_page_url": "http://montanabackend.test//api/ampliacion-cupo?page=2",
        "next_page_url": "http://montanabackend.test//api/ampliacion-cupo?page=2",
        "path": "http://montanabackend.test//api/ampliacion-cupo",
        "per_page": 10,
        "prev_page_url": null,
        "to": 10,
        "total": 14
    }
}
```

------------------------------------------

##### Crear ampliación de cúpo

```js
// Request
axios.post(`${apiUrl}/ampliacion-cupo`, {
    vendedor: 3,
    cliente: 4,
    monto: 300000,
    doc_identidad: 'file.png', // Archivo a subir, max: 2mb
    doc_rut: 'file.png', // Archivo a subir, max: 2mb
    doc_camara_com: 'file.png', // Archivo a subir, max: 2mb
});
```

```json
// Respuesta
{
    "ampliacion_cupo_id": 23,
    "response": "success",
    "status": 200
}
```

------------------------------------------

##### Actualizar ampliación de cúpo

```js
// Request
axios.put(`${apiUrl}/ampliacion-cupo/23`, {
    vendedor: 3,
    cliente: 4,
    monto: 430000,
    doc_identidad: 'file.png', // [Opcional] Archivo a subir, max: 2mb
    doc_rut: 'file.png', // [Opcional] Archivo a subir, max: 2mb
    doc_camara_com: 'file.png', // [Opcional] Archivo a subir, max: 2mb
});
```

```json
// Respuesta
{
    "ampliacion_cupo_id": 23,
    "response": "success",
    "status": 200
}
```

------------------------------------------

##### Obtener usuarios por rol

```js
// Request
axios.get(`${apiUrl}/getUserSmall/3?page=1`);
```

```json
// Respuesta
{
    "response": "success",
    "status": 200,
    "users": {
        "current_page": 1,
        "data": [
            {
                "id": 309,
                "name": "ALFONSO SANABRIA JOAQUIN",
                "apellidos": ""
            },
            {
                "id": 310,
                "name": "ALIANZA MABLE SAS",
                "apellidos": ""
            }
        ],
        "first_page_url": "http://montanabackend.test//api/getUserSmall/3?page=1",
        "from": 1,
        "last_page": 57,
        "last_page_url": "http://montanabackend.test//api/getUserSmall/3?page=57",
        "next_page_url": "http://montanabackend.test//api/getUserSmall/3?page=2",
        "path": "http://montanabackend.test//api/getUserSmall/3",
        "per_page": 20,
        "prev_page_url": null,
        "to": 20,
        "total": 1125
    }
}
```

------------------------------------------

##### Cambiar estado se solicitud de amplicación de cúpo

```js
// Request
axios.post(`${apiUrl}/cambiar-estado/26/aceptado`, {}); // aceptado, rechazado, pendiente
```

```json
// Respuesta
{
    "response": "success",
    "status": 200
}
```