# Guía completa del API de Inmuebles

Esta guía describe el flujo end-to-end para preparar el backend, generar credenciales, autenticar solicitudes y consumir los endpoints JSON disponibles bajo el prefijo `/api/v1`.

## 1. Preparación del entorno 🧰

1. **Configura variables de entorno** en tu `.env` (o `.env.production`) para habilitar el API:
   - `API_JWT_SECRET`: clave privada usada para firmar tokens JWT HS256.
   - `API_JWT_TTL`: tiempo de vida del token en segundos (por defecto 3600).
   - `API_JWT_ISSUER`: identificador opcional del emisor; si lo omites se usa `APP_URL`.
   - `API_ALLOWED_ORIGINS`: lista separada por comas con los dominios autorizados a consumir el API vía navegador (por ejemplo dominios en AWS).

2. **Ejecuta la migración de API keys** o replica su estructura en tu base de datos. La tabla `api_keys` almacena el nombre de referencia, un prefijo visible, el hash SHA-256 de la clave y el último uso registrado.【F:database/migrations/2025_01_01_000000_create_api_keys_table.php†L9-L23】

3. **Verifica el registro del middleware** en `bootstrap/app.php`. El grupo `api` utiliza `AuthenticateApiRequest`, que acepta tokens Bearer y cabeceras `X-Api-Key` para proteger las rutas.【F:bootstrap/app.php†L33-L47】【F:app/Http/Middleware/AuthenticateApiRequest.php†L16-L74】

## 2. Generar API keys desde el panel 🔑

1. Ingresa al backend con tu usuario interno y abre **Configuración → API Keys**. La vista muestra un formulario para asignar un nombre descriptivo (por ejemplo, “AWS Lambda Producción”) y lista las claves existentes con su prefijo y último uso.【F:resources/views/settings/api-keys/index.blade.php†L1-L95】

2. Al enviar el formulario se crea una nueva clave mediante `ApiKey::generateKeyPair()`. El sistema genera un valor aleatorio con prefijo identificable, calcula su hash y garantiza que no exista duplicado antes de guardarlo.【F:app/Models/ApiKey.php†L33-L54】

3. La interfaz muestra el `access_token` **solo una vez**. Copia y guarda el valor completo con el prefijo legible (por ejemplo `ABCD-1234…`); corresponde a la clave original y nosotros almacenamos únicamente su hash para validaciones posteriores.【F:resources/views/settings/api-keys/index.blade.php†L15-L40】【F:app/Models/ApiKey.php†L13-L24】

4. En cualquier momento puedes revocar una clave. El registro se elimina y las solicitudes que usen esa API key dejarán de autenticarse.【F:resources/views/settings/api-keys/index.blade.php†L57-L88】

## 3. Solicitar un token JWT paso a paso 🪪

1. Envía una petición `POST /api/v1/auth/token` con `email` y `password` válidos. El controlador valida las credenciales usando el guard `web` y, si son correctas, emite un token HS256 con el ID del usuario como `sub`.【F:routes/api.php†L10-L18】【F:app/Http/Controllers/Api/AuthenticationController.php†L17-L41】

   ```json
   {
     "email": "admin@example.com",
     "password": "tu-contraseña"
   }
   ```

2. La respuesta incluye `token_type`, `access_token` y `expires_in`. Conserva el valor y úsalo dentro del tiempo configurado en `API_JWT_TTL`.【F:app/Http/Controllers/Api/AuthenticationController.php†L33-L41】

3. Envía el token en cada solicitud protegida usando la cabecera `Authorization: Bearer <token>`.

## 4. Autenticación con API key paso a paso 🧾

Sigue este checklist cada vez que quieras consumir el API con una API key en lugar de un token Bearer:

1. **Genera y copia la clave** como se describe en la sección anterior. Identifica también la IP autorizada si configuraste filtrado desde la vista `/settings/api-keys`.

2. **Identifica el endpoint** que necesitas consumir. Todos viven bajo el prefijo `/api/v1` y requieren HTTPS en entornos públicos.

3. **Arma tu solicitud** en la herramienta de tu preferencia (curl, Postman, axios, etc.) agregando la cabecera `X-Api-Key: TU_API_KEY`. El middleware acepta la clave original mostrada en pantalla y, para compatibilidad, también un hash hexadecimal válido; calcula el hash solo cuando es necesario y recupera al usuario dueño de la clave.【F:app/Http/Middleware/AuthenticateApiRequest.php†L46-L85】

4. **Envía la petición**. Si la clave es válida, se registra la marca de tiempo `last_used_at` (con un límite de actualización de un minuto para evitar escrituras innecesarias) y la solicitud continúa autenticada con el usuario asociado.【F:app/Models/ApiKey.php†L25-L32】【F:app/Http/Middleware/AuthenticateApiRequest.php†L64-L73】

5. **Controla los errores** revisando el código de estado. Un `401` indica que la clave no existe, fue revocada o no coincide con la IP permitida.

### Ejemplo con curl

```bash
curl \
  -H "X-Api-Key: TU_API_KEY" \
  -H "Accept: application/json" \
  https://tu-dominio.com/api/v1/inmuebles
```

### Ejemplo con axios

```js
import axios from 'axios';

const client = axios.create({
  baseURL: 'https://tu-dominio.com/api/v1',
  headers: {
    'X-Api-Key': 'TU_API_KEY',
    Accept: 'application/json',
  },
});

const respuesta = await client.get('/inmuebles');
console.log(respuesta.data);
```

## 5. Autenticación con Bearer token paso a paso 🪪

1. Envía una petición `POST /api/v1/auth/token` con `email` y `password` válidos. El controlador valida las credenciales usando el guard `web` y, si son correctas, emite un token HS256 con el ID del usuario como `sub`.【F:routes/api.php†L10-L18】【F:app/Http/Controllers/Api/AuthenticationController.php†L17-L41】

2. Conserva el `access_token` de la respuesta y úsalo dentro del tiempo configurado en `API_JWT_TTL`.【F:app/Http/Controllers/Api/AuthenticationController.php†L33-L41】

3. Añade la cabecera `Authorization: Bearer <token>` en cada solicitud protegida.

### Ejemplo con curl

```bash
curl \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json" \
  https://tu-dominio.com/api/v1/inmuebles/123
```

### Ejemplo con axios

```js
import axios from 'axios';

const client = axios.create({
  baseURL: 'https://tu-dominio.com/api/v1',
  headers: {
    Authorization: 'Bearer TU_TOKEN',
    Accept: 'application/json',
  },
});

const respuesta = await client.get('/inmuebles/123');
console.log(respuesta.data);
```

## 6. Qué hace el sistema en cada solicitud ⚙️

1. **Recibe la petición** y verifica primero si hay cabecera Bearer. Si existe, intenta decodificar el JWT con `ApiTokenService`.
   - Valida formato (`header.payload.signature`), algoritmo HS256, firma y expiración antes de aceptar el token.【F:app/Services/ApiTokenService.php†L22-L72】
   - Busca al usuario (`sub`) y, si existe, lo asigna como usuario autenticado de la petición.【F:app/Http/Middleware/AuthenticateApiRequest.php†L30-L45】

2. **Si no hay Bearer, busca `X-Api-Key`.** Con el hash SHA-256 se localiza la clave persistida y se obtiene el usuario vinculado.【F:app/Http/Middleware/AuthenticateApiRequest.php†L46-L63】

3. **Sin credenciales válidas,** responde con `401 Unauthorized` y cabecera `WWW-Authenticate: Bearer` para indicar que se requiere autenticación.【F:app/Http/Middleware/AuthenticateApiRequest.php†L24-L33】

## 7. Consumir los endpoints disponibles 📡

Actualmente el API expone los recursos de inmuebles y contactos:

1. **Listado paginado:** `GET /api/v1/inmuebles`
   - Acepta filtros `search`, `page`, `limit`, `operacion`, `estatus` y `destacado`, validados por `IndexInmuebleRequest` antes de ejecutar la consulta.【F:app/Http/Requests/Api/IndexInmuebleRequest.php†L15-L43】
   - `InmuebleController@index` aplica los filtros sobre el modelo, ordena por destacados y fechas, y responde con un `JsonResource` que incluye metadatos de paginación y los filtros aplicados.【F:app/Http/Controllers/Api/InmuebleController.php†L13-L47】

2. **Detalle individual:** `GET /api/v1/inmuebles/{id}`
   - Carga imágenes, estatus y demás atributos antes de serializar el recurso con `InmuebleResource`, que devuelve datos estructurados en JSON (precio formateado, amenidades, URLs, etc.).【F:app/Http/Controllers/Api/InmuebleController.php†L49-L55】【F:app/Http/Resources/InmuebleResource.php†L15-L46】

3. **Búsqueda por slug:** `GET /api/v1/inmuebles/search-by-slug/{slug}`
   - Usa el slug como identificador único para encontrar el inmueble exacto, evitando depender de la paginación del listado general.【F:routes/api.php†L16-L20】
   - Retorna el mismo payload que el endpoint de detalle e incluye imágenes, estatus y demás atributos relevantes. Si el slug no existe responde con un `404` y un mensaje descriptivo.【F:app/Http/Controllers/Api/InmuebleController.php†L57-L73】

### Contactos

1. **Registrar un contacto:** `POST /api/v1/contactos`
   - Valida nombre, email, teléfono, estado y fuente mediante `StoreContactRequest` antes de persistir el registro.【F:routes/api.php†L23-L26】【F:app/Http/Requests/Api/StoreContactRequest.php†L9-L21】
   - Devuelve el recurso recién creado en formato `ContactResource`.

2. **Consultar un contacto:** `GET /api/v1/contactos/{id}`
   - Incluye comentarios ordenados por fecha, interacciones con IA y el historial de intereses con su inmueble asociado. El recurso contiene además el último interés (`interes_reciente`) para facilitar integraciones con bots.【F:routes/api.php†L26-L27】【F:app/Http/Controllers/Api/ContactController.php†L39-L63】【F:app/Http/Resources/ContactResource.php†L17-L26】

3. **Adjuntar un inmueble de interés:** `POST /api/v1/contactos/{id}/intereses`
   - El `StoreContactInterestRequest` exige que `inmueble_id` esté presente y exista en la tabla `inmuebles` antes de crear o refrescar el registro.【F:routes/api.php†L32-L33】【F:app/Http/Requests/Api/StoreContactInterestRequest.php†L9-L19】
   - Si el contacto ya tenía interés en el inmueble, la marca de tiempo `created_at` se actualiza para reflejar la interacción más reciente; de lo contrario, se crea un registro nuevo. La respuesta devuelve el `ContactResource` con los intereses ordenados y el inmueble cargado.【F:app/Http/Controllers/Api/ContactController.php†L65-L95】【F:app/Http/Resources/ContactInterestResource.php†L10-L19】

## 7. Manejo de errores y caducidad 🚨

- Los tokens JWT expiran según `API_JWT_TTL`. Debes solicitar uno nuevo cuando recibas un 401 debido a expiración.
- Las API keys revocadas o inexistentes generan la misma respuesta 401.
- El middleware registra excepciones de token inválido para su monitoreo (`report($exception)`), pero nunca expone detalles sensibles al consumidor final.【F:app/Http/Middleware/AuthenticateApiRequest.php†L34-L38】

## 8. Buenas prácticas finales ✅

- Mantén el secreto JWT y las API keys fuera de repositorios públicos.
- Usa HTTPS en producción para proteger las credenciales en tránsito.
- Revisa periódicamente la columna `last_used_at` para detectar claves obsoletas y revocarlas.

Con estas instrucciones podrás preparar el entorno, emitir credenciales y consumir los endpoints del API de forma segura.
