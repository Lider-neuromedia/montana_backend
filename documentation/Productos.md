# API - Catálogos

```js
const apiUrl = "http://montanabackend.test/api";
```

------------------------------------------

##### Obtener marcas

```js
// Request
axios.get(`${apiUrl}/marcas`);
```

```json
// Respuesta
{
    "response": "success",
    "status": 200,
    "marcas": [
        {
            "id_marca": 1,
            "nombre_marca": "ATHLETIC",
            "codigo": "01"
        },
        {
            "id_marca": 10,
            "nombre_marca": "CALZADO ESCOLAR",
            "codigo": "16"
        },
    ]
}
```

------------------------------------------

##### Obtener productos por categoría

```js
// Request
axios.get(`${apiUrl}/productos/3?page=1`);
```

```json
// Respuesta
{
    "response": "success",
    "status": 200,
    "productos": {
        "current_page": 1,
        "data": [
            {
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
                "catalogo": 3,
                "marca": {
                    "id_marca": 1,
                    "nombre_marca": "ATHLETIC",
                    "codigo": "01"
                },
                "created_at": "2020-10-27 08:08:49",
                "updated_at": "2022-02-18 14:57:30",
                "marca_id": 1,
                "image": "http://montanabackend.test/storage/productos/3/ATH-30303/ATH-30303-0.png"
            },
        ],
        "first_page_url": "http://montanabackend.test//api/productos/3?page=1",
        "from": 1,
        "last_page": 137,
        "last_page_url": "http://montanabackend.test//api/productos/3?page=137",
        "next_page_url": "http://montanabackend.test//api/productos/3?page=2",
        "path": "http://montanabackend.test//api/productos/3",
        "per_page": 20,
        "prev_page_url": null,
        "to": 20,
        "total": 2733
    }
}
```

------------------------------------------

##### Obtener productos de Show Room

```js
// Request
axios.get(`${apiUrl}/getProductsShowRoom?page=50`);
```

```json
// Respuesta
{
    "productos": {
        "current_page": 1,
        "data": [
            {
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
                "catalogo": 3,
                "marca": {
                    "id_marca": 1,
                    "nombre_marca": "ATHLETIC",
                    "codigo": "01"
                },
                "created_at": "2020-10-27 08:08:49",
                "updated_at": "2022-02-18 14:57:30",
                "marca_id": 1,
                "image": "http://montanabackend.test/storage/productos/3/ATH-30303/ATH-30303-0.png"
            }
        ],
        "first_page_url": "http://montanabackend.test//api/getProductsShowRoom?page=1",
        "from": 1,
        "last_page": 137,
        "last_page_url": "http://montanabackend.test//api/getProductsShowRoom?page=137",
        "next_page_url": "http://montanabackend.test//api/getProductsShowRoom?page=2",
        "path": "http://montanabackend.test//api/getProductsShowRoom",
        "per_page": 20,
        "prev_page_url": null,
        "to": 20,
        "total": 2733
    },
    "response": "success",
    "status": 200
}
```

------------------------------------------

##### Obtener detalle de producto

```js
// Request
axios.get(`${apiUrl}/producto/22`);
```

```json
// Respuesta
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
        "catalogo": 3,
        "marca": {
            "id_marca": 1,
            "nombre_marca": "ATHLETIC",
            "codigo": "01"
        },
        "created_at": "2020-10-27 08:08:49",
        "updated_at": "2022-02-18 14:57:30",
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
            }
        ]
    },
    "response": "success",
    "status": 200
}
```

------------------------------------------

##### Crear Producto

```js
// Request
axios.post(`${apiUrl}/productos`, {
    nombre: 'Tenis Puma Adulto',
    codigo: '03030387',
    referencia: 'ATH-50504',
    sku: '50504',
    descripcion: 'Tenis Puma Adulto Home, descripción de prueba',
    stock: 50,
    precio: 270000,
    total: 270000,
    iva: 19,
    marca_id: 2,
    catalogo_id: 3,
    imagenes: [
        {
            file: 'imagen.png', // Archivo nuevo, Maximo 2mb
            destacada: 1, // 1: true, 0: false
        },
        {
            file: 'imagen.png',
            destacada: 0,
        }
    ]
});
```

```json
// Respuesta
{
    "message": "Producto guardado correctamente.",
    "response": "success",
    "producto_id": 2764,
    "status": 200
}
```

------------------------------------------

##### Actualizar Producto

```js
// Request
axios.put(`${apiUrl}/producto/60`, {
    nombre: 'Tenis Puma Adulto',
    codigo: '03030387',
    referencia: 'ATH-50504',
    sku: '50504',
    descripcion: 'Tenis Puma Adulto Home, descripción de prueba',
    stock: 50,
    precio: 270000,
    total: 270000,
    iva: 19,
    marca_id: 2,
    catalogo_id: 3,
    imagenes: [ // Si una imagen no aparece aquí con el id, se borra
        {
            id: 1,
            destacada: 1,
        }, {
            id: 2,
            destacada: 0,
        },  { // Imagen nueva
            file: 'imagen.png', // Archivo nuevo, Maximo 2mb
            destacada: 0,
        }
    ]
});
```

```json
// Respuesta
{
    "message": "Producto guardado correctamente.",
    "response": "success",
    "producto_id": 2764,
    "status": 200
}
```

------------------------------------------

##### Borrar producto

```js
// Request
axios.get(`${apiUrl}/producto/2765`);
```

```json
// Respuesta
{
    "message": "Producto eliminado.",
    "response": "success",
    "status": 200
}
```
