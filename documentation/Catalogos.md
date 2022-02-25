# API - Catálogos

```js
const apiUrl = "http://montanabackend.test/api";
```

------------------------------------------

##### Obtener catálogos

```js
// Request
axios.get(`${apiUrl}/catalogos?search={"general":1,"show_room":0,"public":1,"private":1,"ninos":0,"adultos":1}`);
```

```json
// Respuesta
{
    "response": "success",
    "message": "",
    "status": 200,
    "catalogos": [
        {
            "id_catalogo": 3,
            "estado": "privado",
            "tipo": "show room",
            "imagen": "http://montanabackend.test/storage/catalogos/3.png",
            "titulo": "Primavera 2021",
            "cantidad": 2733,
            "descuento": 5,
            "etiqueta": "adultos",
            "created_at": "2022-02-25 09:06:16",
            "updated_at": "2022-02-25 09:06:16"
        },
    ]
}
```

------------------------------------------

##### Obtener catálogos activos

```js
// Request
axios.get(`${apiUrl}/consumerCatalogos`);
```

```json
// Respuesta
{
    "response": "success",
    "status": 200,
    "catalogos": [
        {
            "id_catalogo": 31,
            "estado": "activo",
            "tipo": "general",
            "imagen": "http://montanabackend.test/storage/catalogos/31.png",
            "titulo": "Primavera 2021",
            "cantidad": 4,
            "descuento": null,
            "etiqueta": "adultos",
            "created_at": "2021-03-30 04:10:05",
            "updated_at": "2022-02-25 09:06:16"
        }
    ]
}
```

------------------------------------------

##### Obtener detalle de catálogo

```js
// Request
axios.get(`${apiUrl}/catalogos/31`);
```

```json
// Respuesta
{
    "id_catalogo": 31,
    "estado": "activo",
    "tipo": "general",
    "imagen": "storage/catalogos/31.png",
    "titulo": "Primavera 2021",
    "cantidad": 4,
    "descuento": 0,
    "etiqueta": "adultos",
    "created_at": "2021-03-30 04:10:05",
    "updated_at": "2022-02-25 09:06:16"
}
```

------------------------------------------

##### Crear catálogo

```js
// Request
axios.post(`${apiUrl}/catalogos`, {
    estado: 'activo', // activo, privado
    tipo: 'show room', // show room, general
    etiqueta: 'adultos', // adultos, niños
    titulo: 'Zapatos de Prueba',
    image: 'image.png', // Archivo a subir, 2mb máximo
});
```

```json
// Respuesta
{
    "catalogo": {
        "titulo": "Zapatos de Prueba",
        "etiqueta": "adultos",
        "tipo": "show room",
        "estado": "activo",
        "cantidad": 0,
        "descuento": 0,
        "updated_at": "2022-02-25 11:51:36",
        "created_at": "2022-02-25 11:51:36",
        "id_catalogo": 65,
        "imagen": "storage/catalogos/65.png"
    },
    "response": "success",
    "status": 200
}
```

------------------------------------------

##### Actualizar catálogo

```js
// Request
axios.put(`${apiUrl}/catalogos/65`, {
    estado: 'activo', // activo, privado
    tipo: 'show room', // show room, general
    etiqueta: 'adultos', // adultos, niños
    titulo: 'Zapatos de Prueba',
    image: 'image.png', // Archivo a subir, 2mb máximo [OPCIONAL]
});
```

```json
// Respuesta
{
    "catalogo": {
        "id_catalogo": 66,
        "estado": "activo",
        "tipo": "general",
        "imagen": "storage/catalogos/66.png",
        "titulo": "Zapatos de Prueba 2",
        "cantidad": 0,
        "descuento": 0,
        "etiqueta": "adultos",
        "created_at": "2022-02-25 11:53:19",
        "updated_at": "2022-02-25 11:57:19"
    },
    "response": "success",
    "status": 200
}
```

------------------------------------------

##### Borrar catálogo por id (no se puede borrar si tiene productos asignados)

```js
// Request
axios.delete(`${apiUrl}/catalogos/64`);
```

```json
// Respuesta
{
    "message": "Catálogo borrado correctamente.",
    "response": "success",
    "status": 200
}
```
