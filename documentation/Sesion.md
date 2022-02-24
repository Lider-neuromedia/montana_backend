# API - Sesión

```js
const apiUrl = "http://ganeraspa.test/api";
```

------------------------------------------

##### Iniciar sesión
```js
// Request
axios.post(`${apiUrl}/auth/login`, {
    email: 'example@mail.com',
    password: 'secret',
});
```

```json
// Respuesta
{
    "access_token": "eyJ0eXAiOiJKV1....",
    "token_type": "Bearer",
    "expires_at": "2023-02-23 08:52:13",
    // all, catalogos, pedidos, showRoom, clientes, pqrs, ampliacion_cupo
    "permisos": [ "all" ],
    "id": 214,
    "email": "example@mail.com",
    "name": "John",
    "apellidos": "Doe",
    "rol": 1,
    "dni": "123456789",
    "tipo_identificacion": "Cedula",
    "datos": [
        {
            "id": 198,
            "user_id": 214,
            "field_key": "telefono",
            "value_key": "3206006050"
        }
    ]
}
```

------------------------------------------

##### Registrarme

```js
// Request
axios.post(`${apiUrl}/auth/signup`, {
    email: 'admin2@montana.com',
    password: 'montana000',
    password_confirmation: 'montana000',
    rol_id: 3, // 2: Vendedor, 3: Cliente
    name: 'John Doe',
});
```

```json
// Respuesta
{
    "message": "Usuario creado correctamente."
}
```

------------------------------------------

##### Cerrar sesión

```js
// Request
// El token para cerrar sesión debe ir en la cabecera de la request.
axios.get(`${apiUrl}/auth/logout`);
```

```json
// Respuesta
{
    "message": "Sesión cerrada correctamente"
}
```

------------------------------------------

##### Obtener usuario logueado

```js
// Request
axios.get(`${apiUrl}/auth/user`);
```

```json
// Respuesta
{
    "id": 214,
    "rol_id": 1,
    "name": "John",
    "apellidos": "Doe",
    "email": "example@mail.com",
    "tipo_identificacion": "Cedula",
    "dni": "123456789",
    "created_at": "2021-04-08 21:42:52",
    "updated_at": "2021-04-08 21:42:52",
    "datos": [
        {
            "id": 198,
            "user_id": 214,
            "field_key": "telefono",
            "value_key": "3206060500"
        }
    ]
}
```

------------------------------------------

##### Obtener usuario logueado con accesos

```js
// Request
axios.get(`${apiUrl}/auth/getUserSesion`);
```

```json
// Respuesta
{
    "response": "success",
    "status": 200,
    "user": {
        "id": 214,
        "rol_id": 1,
        "name": "John",
        "apellidos": "Doe",
        "email": "example@mail.com",
        "tipo_identificacion": "Cedula",
        "dni": "123456789",
        "created_at": "2021-04-08 21:42:52",
        "updated_at": "2021-04-08 21:42:52"
    },
    "datos": [
        {
            "id": 198,
            "user_id": 214,
            "field_key": "telefono",
            "value_key": "3206066557"
        }
    ],
    // all, catalogos, pedidos, showRoom, clientes, pqrs, ampliacion_cupo
    "permisos": [ "all" ]
}
```

------------------------------------------

##### Obtener código por correo para restaurar contraseña

```js
// Request
axios.post(`${apiUrl}/password/email`, {
    email: "example@mail.com",
});
```

```json
// Respuesta
{
    "message": "Correo con token de reinicio de contraseña enviado."
}
```

------------------------------------------

##### Restaurar contraseña

```js
// Request
axios.post(`${apiUrl}/password/reset`, {
    token: 'l4yBw7A3n9yxbtMMS9P16xEc',
    email: 'example@mail.com',
    password: 'secret',
    password_confirmation: 'secret',
});
```

```json
// Respuesta
{
    "message": "Contraseña actualizada correctamente."
}
```

------------------------------------------

##### Registrar token de dispositivo móvil

```js
// Request
axios.post(`${apiUrl}/devices`, {
    device_token:"e9_F_ZgeQkqmZrNBT8oS..."
});
```

```json
// Respuesta
{
    "response": "success",
    "message": "Token de dispositivo guardado.",
    "status": 200
}
```

------------------------------------------

##### Obtener resumen de estadísticas para dashboard

```js
// Request
axios.get(`${apiUrl}/dashboard-resumen?fecha_atendidos=2020-01`);
```

```json
// Respuesta: Sesión vendedor
{
    "cantidad_clientes": 5,
    "cantidad_clientes_atendidos": 0,
    "cantidad_pedidos": {
        "realizados": 5,
        "aprobados": 3,
        "rechazados": 2,
        "pendientes": 0
    }
}
```

```json
// Respuesta: Sesión cliente
{
    "cantidad_tiendas": 6,
    "cantidad_pqrs": 2,
    "cantidad_pedidos": {
        "realizados": 5,
        "aprobados": 1,
        "pendientes": 1,
        "rechazados": 3
    }
}
```
