# API - Importar Base de Datos

```js
const apiUrl = "http://montanabackend.test/api";
```

------------------------------------------

##### Importar Marcas

```js
// Request
axios.post(`${apiUrl}/batch/importar-marcas`, {
    archivo: 'file.csv', // Archivo CSV. max: 300kb
});
```

```json
// Respuesta
{
    "registros_ingresados": 30,
    "marcas_guardadas": 30,
    "marcas_total": 35 // Registros nuevos + registros ya existentes
}
```

------------------------------------------

##### Importar Productos

```js
// Request
axios.post(`${apiUrl}/batch/importar-productos`, {
    archivo: 'file.csv', // Archivo CSV. max: 300kb
});
```

```json
// Respuesta
{
    "registros_ingresados": 1000,
    "productos_guardados": 1000,
    "productos_total": 1023 // Registros nuevos + registros ya existentes
}
```

------------------------------------------

##### Importar Vendedores

```js
// Request
axios.post(`${apiUrl}/batch/importar-vendedores`, {
    archivo: 'file.csv', // Archivo CSV. max: 300kb
});
```

```json
// Respuesta
{
    "registros_ingresados": 500,
    "registros_guardados": 499,
    "vendedores_total": 550  // Registros nuevos + registros ya existentes
}
```

------------------------------------------

##### Importar Clientes

```js
// Request
axios.post(`${apiUrl}/batch/importar-clientes`, {
    archivo: 'file.csv', // Archivo CSV. max: 300kb
});
```

```json
// Respuesta
{
    "registros_ingresados": 500,
    "registros_guardados": 498,
    "clientes_total": 528, // Registros nuevos + registros ya existentes
    "tiendas_total": 600 // Registros nuevos + registros ya existentes
}
```

------------------------------------------

##### Importar Cartera

```js
// Request
axios.post(`${apiUrl}/batch/importar-cartera`, {
    archivo: 'file.csv', // Archivo CSV. max: 300kb
});
```

```json
// Respuesta
{
    "registros_ingresados": 450,
    "carteras_guardadas": 450,
    "carteras_total": 470 // Registros nuevos + registros ya existentes
}
```