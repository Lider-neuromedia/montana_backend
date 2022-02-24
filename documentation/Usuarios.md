# API - Sesión

```js
const apiUrl = "http://montanabackend.test/api";
```

------------------------------------------

##### Obtener listado de usuarios paginado
```js
// Request
axios.get(`${apiUrl}/users?page=1`);
```

```json
// Respuesta
{
    "current_page": 1,
    "data": [
        {
            "id": 3,
            "rol_id": 2,
            "name": "John",
            "apellidos": "Doe",
            "email": "example@mail.com",
            "tipo_identificacion": "Cedula",
            "dni": "452524",
            "created_at": "2020-07-30 07:18:25",
            "updated_at": "2021-03-30 19:44:30"
        },
        {
            "id": 4,
            // ...
        },
    ],
    "first_page_url": "http://montanabackend.test//api/users?page=1",
    "from": 1,
    "last_page": 301,
    "last_page_url": "http://montanabackend.test//api/users?page=301",
    "next_page_url": "http://montanabackend.test//api/users?page=2",
    "path": "http://montanabackend.test//api/users",
    "per_page": 4,
    "prev_page_url": null,
    "to": 4,
    "total": 1201
}
```

------------------------------------------

##### Crear Usuario

```js
// Request
axios.post(`${apiUrl}/users`, {
    rol_id: 2, // 2 Vendedor, 3 Cliente, 1 Administrador
    name: 'John',
    apellidos: 'Doe',
    email: 'johndoe@mail.com',
    tipo_identificacion: 'Cedula',
    dni: '613481684',
    password: 'montana000',
    datos: [
        // Datos Administrador
        {
            field_key: 'telefono',
            value_key: '3100201245',
        },
        // Datos Vendedor
        {
            field_key: 'telefono',
            value_key: '3100201245',
        },
        {
            field_key: 'codigo',
            value_key: '1',
        },
        // Datos Cliente
        {
            field_key: 'nit',
            value_key: '65123158',
        },
        {
            field_key: 'razon_social',
            value_key: 'Nombre Empresa',
        },
        {
            field_key: 'direccion',
            value_key: 'Calle 123 # 12 - 44',
        },
        {
            field_key: 'telefono',
            value_key: '3102015465',
        },
        {
            field_key: 'codigo',
            value_key: '1',
        },
    ],
    // Datos obligatorios para vendedores
    clientes: [ // Tiendas asignadas a vendedores.
        {
            cliente_id: 4,
            tienda_id: 1,
        },
        {
            cliente_id: 4,
            tienda_id: 2,
        },
        {
            cliente_id: 133,
            tienda_id: 17,
        },
    ],
    // Datos obligatorios para clientes.
    tiendas: [
        {
            nombre: 'Evacol',
            lugar: 'Bogotá calima',
            local: 'CC Llanogrande',
            direccion: 'C.C LLANO GRANDE LOCAL 130 CL 31 44-239 VIA LAS PL',
            telefono: '2439232 EXT 201',
            sucursal: 'K', // Texto vacío, Letras, Números, ej: 1,2,4,K,J,A
            fecha_ingreso: '2017-08-23',
            fecha_ultima_compra: '2017-09-29',
            cupo: 0, // $$$
            ciudad_codigo: '001',
            zona: '11', // Número, Texto vacío
            bloqueado: 'N', // N: desbloqueado, S: bloqueado
            bloqueado_fecha: '', // yyyy-mm-dd
            nombre_representante: '',
            plazo: 0, // días
            escala_factura: 'A', // A, B, (Texto vacío)
            observaciones: 'Texto de observación',
            vendedores: [3, 136], // Ids usuarios vendedores
        }
    ]
});
```

```json
// Respuesta
{
    "response": "success",
    "status": 200,
    "message": "Usuario creado de manera correcta!"
}
```

------------------------------------------

##### Actualizar usuario (Cliente, Vendedor, Administrador)

```js
// Request

// '${apiUrl}/update-cliente/1413' ENDPOINT ELIMINADO
// '${apiUrl}/update-vendedor/1413' ENDPOINT ELIMINADO

axios.post(`${apiUrl}/update-user/1413`, {
    rol_id: 2, // 2 Vendedor, 3 Cliente, 1 Administrador
    name: 'John',
    apellidos: 'Doe',
    email: 'johndoe2@mail.com',
    tipo_identificacion: 'Cedula',
    dni: '613481684',
    password: 'montana000',
    datos: [
        // Datos Administrador
        {
            field_key: 'telefono',
            value_key: '3100201245',
        },
        // Datos Vendedor
        {
            field_key: 'telefono',
            value_key: '3100201245',
        },
        {
            field_key: 'codigo',
            value_key: '1',
        },
        // Datos Cliente
        {
            field_key: 'nit',
            value_key: '65123158',
        },
        {
            field_key: 'razon_social',
            value_key: 'Nombre Empresa',
        },
        {
            field_key: 'direccion',
            value_key: 'Calle 123 # 12 - 44',
        },
        {
            field_key: 'telefono',
            value_key: '3102015465',
        },
        {
            field_key: 'codigo',
            value_key: '1',
        },
    ],
    // Datos obligatorios para vendedores.
    clientes: [ // Tiendas asignadas a vendedores.
        {
            cliente_id: 4,
            tienda_id: 1,
        },
        {
            cliente_id: 4,
            tienda_id: 2,
        },
        {
            cliente_id: 133,
            tienda_id: 17,
        },
    ],
    // Datos obligatorios para clientes.
    tiendas: [
        {
            id: 2, // el id puede ser nulo si la tienda es nueva
            nombre: 'Evacol',
            lugar: 'Bogotá calima',
            local: 'CC Llanogrande',
            direccion: 'C.C LLANO GRANDE LOCAL 130 CL 31 44-239 VIA LAS PL',
            telefono: '2439232 EXT 201',
            sucursal: 'k', // Texto vacío, Letras, Números, ej: 1,2,4,K,J,A
            fecha_ingreso: '2017-08-23',
            fecha_ultima_compra: '2017-09-29',
            cupo: 0, // $$$
            ciudad_codigo: '001',
            zona: '11', // Número, Texto vacío
            bloqueado: 'N', // N: desbloqueado, S: bloqueado
            bloqueado_fecha: '', // yyyy-mm-dd
            nombre_representante: '',
            plazo: 0 // días
            escala_factura: 'A', // A, B, (Texto vacío)
            observaciones: 'Texto de observación',
            vendedores:[ 3, 136 ], // Ids usuarios vendedores
        }
    ],
});
```

```json
// Respuesta
{
    "response": "success",
    "message": "Usuario actualizado con exito.",
    "status": 200
}
```

------------------------------------------

##### Eliminar usuarios

```js
// Request
axios.post(`${apiUrl}/delete-users`, {
    usuarios: [
        1411,
        108,
    ]
});
```

```json
// Respuesta
{
    "response": "success",
    "message": "Usuario eliminado correctamente",
    "status": 200
}
```

------------------------------------------

##### Obtener usuarios por rol

```js
// Request
// 1 Administrador, 2 Vendedor, 3 Cliente
axios.get(`${apiUrl}/user-rol/3?page=1`);
```

```json
// Respuesta
{
    "current_page": 1,
    "data": [
        {
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
        // ...
    ],
    "first_page_url": "http://montanabackend.test//api/user-rol/3?page=1",
    "from": 1,
    "last_page": 113,
    "last_page_url": "http://montanabackend.test//api/user-rol/3?page=113",
    "next_page_url": "http://montanabackend.test//api/user-rol/3?page=2",
    "path": "http://montanabackend.test//api/user-rol/3",
    "per_page": 10,
    "prev_page_url": null,
    "to": 10,
    "total": 1125
}
```

------------------------------------------

##### Obtener roles de usuario

```js
// Request
axios.get(`${apiUrl}/roles`);
```

```json
// Respuesta
[
    {
        "id": 1,
        "name": "administrador"
    },
    {
        "id": 2,
        "name": "vendedor"
    },
    {
        "id": 3,
        "name": "cliente"
    }
]
```