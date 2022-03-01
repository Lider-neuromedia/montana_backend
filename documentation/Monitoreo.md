# API - Monitoreo

```js
const apiUrl = "http://montanabackend.test/api";
```

------------------------------------------

##### Obtener logs de monitoreo

```js
// Request
axios.get(`${apiUrl}/monitoreo`);
```

```json
// Respuesta
{
    "audits": {
        "current_page": 1,
        "data": [
            {
                "id": 107142,
                "user_type": "App\\Entities\\User",
                "user_id": 214,
                "event": "deleted",
                "auditable_type": "App\\Entities\\Producto",
                "auditable_id": 2765,
                "old_values": {
                    "id_producto": 2765,
                    "nombre": "Tenis Puma Adulto",
                    "codigo": "03030387",
                    "referencia": "ATH-50504",
                    "stock": 50,
                    "precio": 270000,
                    "descripcion": "Tenis Puma Adulto Home, descripción de prueba",
                    "sku": "50504",
                    "total": 270000,
                    "descuento": 0,
                    "iva": 19,
                    "catalogo": 3,
                    "marca": 2
                },
                "new_values": [],
                "url": "http://montanabackend.test//api/producto/2765",
                "ip_address": "192.168.10.1",
                "user_agent": "PostmanRuntime/7.28.4",
                "tags": null,
                "created_at": "2022-02-25 17:59:49",
                "updated_at": "2022-02-25 17:59:49",
                "user": {
                    "id": 214,
                    "rol_id": 1,
                    "name": "Admin",
                    "apellidos": "Montana",
                    "dni": "123456789",
                    "tipo_identificacion": "Cedula"
                }
            },
            {
                "id": 107141,
                "user_type": "App\\Entities\\User",
                "user_id": 214,
                "event": "updated",
                "auditable_type": "App\\Entities\\Catalogo",
                "auditable_id": 3,
                "old_values": {
                    "cantidad": 2739
                },
                "new_values": {
                    "cantidad": 2738
                },
                "url": "http://montanabackend.test//api/producto/2765",
                "ip_address": "192.168.10.1",
                "user_agent": "PostmanRuntime/7.28.4",
                "tags": null,
                "created_at": "2022-02-25 17:59:49",
                "updated_at": "2022-02-25 17:59:49",
                "user": {
                    "id": 214,
                    "rol_id": 1,
                    "name": "Admin",
                    "apellidos": "Montana",
                    "dni": "123456789",
                    "tipo_identificacion": "Cedula"
                }
            }
        ],
        "first_page_url": "http://montanabackend.test//api/monitoreo?page=1",
        "from": 1,
        "last_page": 10711,
        "last_page_url": "http://montanabackend.test//api/monitoreo?page=10711",
        "next_page_url": "http://montanabackend.test//api/monitoreo?page=2",
        "path": "http://montanabackend.test//api/monitoreo",
        "per_page": 10,
        "prev_page_url": null,
        "to": 10,
        "total": 107103
    },
    "response": "success",
    "status": 200
}
```
