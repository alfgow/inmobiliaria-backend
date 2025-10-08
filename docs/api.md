# API de Inmuebles

Esta guía describe la primera versión (`v1`) del API JSON expuesto por el backend. Todos los endpoints están disponibles bajo el prefijo `/api/v1` y responden con JSON UTF-8.

## Autenticación

Los endpoints requieren un token JWT firmado con HS256. Obtén un token válido enviando credenciales de usuario registradas en el backend.

```
POST /api/v1/auth/token
Content-Type: application/json

{
  "email": "usuario@example.com",
  "password": "secreto"
}
```

Respuesta exitosa:

```json
{
  "token_type": "Bearer",
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOi...",
  "expires_in": 3600
}
```

Incluye el token en la cabecera `Authorization` de cada petición protegida:

```
Authorization: Bearer <token>
```

## Listado de inmuebles

```
GET /api/v1/inmuebles?search=&page=1&limit=20
Authorization: Bearer <token>
```

Parámetros soportados:

| Parámetro    | Tipo     | Descripción                                                       |
|--------------|----------|-------------------------------------------------------------------|
| `search`     | string   | Coincidencias parciales contra título, dirección o ubicación.     |
| `page`       | integer  | Página solicitada (comienza en 1).                                |
| `limit`      | integer  | Registros por página (1 - 100, por defecto 20).                   |
| `operacion`  | string   | Filtra por tipo de operación (`Renta`, `Venta`, `Traspaso`).      |
| `estatus`    | integer  | ID de estatus (`inmueble_statuses`).                              |
| `destacado`  | boolean  | `true` o `false` para limitar a inmuebles destacados.             |

La respuesta incluye metadatos de paginación estándar (`links`, `meta`) y los filtros aplicados en la clave `filters`.

## Detalle de un inmueble

```
GET /api/v1/inmuebles/{id}
Authorization: Bearer <token>
```

La respuesta devuelve todos los atributos principales del inmueble, estatus y colecciones de imágenes (`imagen_portada` y `imagenes`). Los campos `amenidades` y `extras` se entregan como arreglos.

## CORS

Los orígenes autorizados se controlan con la variable `API_ALLOWED_ORIGINS` (lista separada por comas). Asegúrate de incluir tus dominios desplegados en AWS para permitir el consumo del API desde el navegador.

## Configuración de JWT

Configura las variables de entorno:

- `API_JWT_SECRET`: clave secreta utilizada para firmar los tokens.
- `API_JWT_TTL`: tiempo de vida del token en segundos (por defecto 3600).
- `API_JWT_ISSUER`: identificador opcional del emisor (se usa `APP_URL` si no se especifica).

Renueva el token generando uno nuevo cuando expire (`expires_in`).
