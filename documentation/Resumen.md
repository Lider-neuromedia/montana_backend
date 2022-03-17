# API - Resumen

```js
const apiUrl = "http://ganeraspa.test/api";
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

------------------------------------------

##### Obtener resumen de cartera de cliente

```js
// Request
axios.get(`${apiUrl}/resumen/cliente/783`);
```

```json
// Respuesta
{
    "cliente_id": 783,
    "cupo_preaprobado": 3000000,
    "cupo_disponible": -7804000,
    "saldo_total_deuda": 10680000,
    "saldo_mora": 9804000
}
```

------------------------------------------

##### Obtener resumen de cartera de vendedor

```js
// Request
axios.get(`${apiUrl}/resumen/vendedor/3`);
```

```json
// Respuesta
{
    "vendedor_id": 3,
    "comisiones_perdidas": 700000,
    "comisiones_proximas_perder": 500000,
    "comisiones_ganadas": 2200000
}
```
