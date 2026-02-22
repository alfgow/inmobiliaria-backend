# 📘 Documentación del API (Inmobiliaria Backend)

Base URL: `/api/v1`

Esta guía resume cómo autenticarte y cómo consumir todos los endpoints disponibles del API.

---

## 1) Autenticación

El API acepta **Bearer JWT** o **API Key** en `X-Api-Key`.

### 1.1 Obtener token JWT

**Endpoint**

`POST /api/v1/auth/token`

**Body (JSON)**

```json
{
  "email": "admin@example.com",
  "password": "secret"
}
```

**Respuesta 200**

```json
{
  "token_type": "Bearer",
  "access_token": "eyJ...",
  "expires_in": 3600
}
```

**Errores comunes**

- `422` credenciales inválidas o formato incorrecto.

### 1.2 Usar Bearer Token

```bash
curl -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json" \
  https://tu-dominio.com/api/v1/inmuebles
```

### 1.3 Usar API Key

```bash
curl -H "X-Api-Key: TU_API_KEY" \
  -H "Accept: application/json" \
  https://tu-dominio.com/api/v1/inmuebles
```

Si no envías credenciales válidas, el API responde `401 Unauthorized`.

---

## 2) Endpoints de Inmuebles

## 2.1 Listar inmuebles

**Endpoint**

`GET /api/v1/inmuebles`

**Query params opcionales**

- `search` (string, máx 255)
- `page` (int >= 1)
- `limit` (int 1..100, default 20)
- `operacion` (uno de los valores permitidos por el modelo)
- `estatus` (id existente en `inmueble_statuses`)
- `destacado` (`true` / `false`)

**Notas**

- Ordena por `destacado DESC` y luego `updated_at DESC`.
- Incluye un objeto `filters` en la respuesta con los filtros aplicados.

### 2.2 Obtener inmueble por ID

**Endpoint**

`GET /api/v1/inmuebles/{inmueble}`

Retorna el recurso completo del inmueble (incluyendo imágenes, estatus y restricciones).

### 2.3 Buscar inmueble por slug

**Endpoint**

`GET /api/v1/inmuebles/search-by-slug/{slug}`

**Respuesta 404 (si no existe):**

```json
{
  "message": "No se encontró un inmueble con el slug proporcionado."
}
```

---

## 3) Endpoints de Contactos

## 3.1 Listar contactos

**Endpoint**

`GET /api/v1/contactos`

**Query params opcionales**

- `telefono` (string, máx 30)
- `email` (email)
- `nombre` (string)
- `limit` (int 1..100, default 15)

**Notas**

- Devuelve paginado.
- Incluye `latestComment` y `latestInterest`.

### 3.2 Crear contacto

**Endpoint**

`POST /api/v1/contactos`

**Body (JSON)**

```json
{
  "nombre": "Juan Pérez",
  "email": "juan@example.com",
  "telefono": "5512345678",
  "estado": "nuevo",
  "fuente": "web"
}
```

**Validación importante**

- `nombre`: requerido
- `telefono`: opcional, pero único en `contactos`

**Respuesta:** `201 Created`.

### 3.3 Ver contacto por ID

**Endpoint**

`GET /api/v1/contactos/{contact}`

Incluye comentarios, interacciones IA e intereses (ordenados por más reciente).

### 3.4 Actualizar contacto completo

**Endpoint**

`PUT/PATCH /api/v1/contactos/{contact}`

**Body (JSON)**

```json
{
  "nombre": "Juan Pérez",
  "email": "juan@nuevo.com",
  "telefono": "5512345678",
  "estado": "en_contacto",
  "fuente": "whatsapp"
}
```

`nombre` es requerido también al actualizar.

### 3.5 Actualizar estado del contacto

**Endpoint**

`PUT/PATCH /api/v1/contactos/{contact}/estado`

**Body (JSON)**

```json
{
  "estado": "convertido"
}
```

**Valores permitidos para `estado`**

- `nuevo`
- `en_contacto`
- `convertido`
- `rechazado`
- `rejected`
- `reject`
- `block`
- `blocked`

### 3.6 Registrar interés de contacto en inmueble

**Endpoint**

`POST /api/v1/contactos/{contact}/intereses`

**Body (JSON)**

```json
{
  "inmueble_id": 123
}
```

Si el interés ya existe para ese contacto + inmueble, se refresca su fecha de creación.

---

## 4) Comentarios de contacto

## 4.1 Listar comentarios

`GET /api/v1/contactos/{contact}/comentarios`

### 4.2 Crear comentario

`POST /api/v1/contactos/{contact}/comentarios`

**Body (JSON)**

```json
{
  "comentario": "Se llamó y pidió más información",
  "created_at": "2025-10-10 12:00:00"
}
```

- `created_at` es opcional.
- Respuesta: `201 Created`.

### 4.3 Actualizar comentario

`PUT/PATCH /api/v1/contactos/{contact}/comentarios/{comentario}`

**Body (JSON)**

```json
{
  "comentario": "Comentario actualizado"
}
```

Si el comentario no pertenece al contacto enviado en la URL, responde `404`.

---

## 5) Interacciones IA de contacto

## 5.1 Listar interacciones IA

`GET /api/v1/contactos/{contact}/interacciones-ia`

### 5.2 Crear interacción IA

`POST /api/v1/contactos/{contact}/interacciones-ia`

**Body (JSON)**

```json
{
  "payload": {
    "role": "assistant",
    "message": "Hola, te ayudo con propiedades en tu zona"
  },
  "created_at": "2025-10-10 12:00:00"
}
```

- `payload` es requerido y debe ser objeto/array JSON.
- `created_at` es opcional.
- Respuesta: `201 Created`.

### 5.3 Actualizar interacción IA

`PUT/PATCH /api/v1/contactos/{contact}/interacciones-ia/{interaccion}`

**Body (JSON)**

```json
{
  "payload": {
    "role": "assistant",
    "message": "Mensaje ajustado"
  }
}
```

Si la interacción no pertenece al contacto enviado en la URL, responde `404`.

---

## 6) Códigos de respuesta frecuentes

- `200 OK`: consulta o actualización exitosa.
- `201 Created`: creación exitosa.
- `401 Unauthorized`: falta autenticación o credencial inválida.
- `404 Not Found`: recurso no encontrado.
- `422 Unprocessable Entity`: error de validación.

---

## 7) Recomendaciones operativas

- Usa siempre `Accept: application/json`.
- En producción, usa HTTPS.
- Rota y revoca API keys periódicamente.
- Maneja reintentos en cliente para errores transitorios, pero no para `422`.

---

## 4) Endpoints de Bot Users

### 4.1 Listar bot users

**Endpoint**

`GET /api/v1/bot-users`

**Query params opcionales**

- `status` (string)
- `bot_status` (string)
- `questionnaire_status` (string)
- `session_id` (string parcial)
- `telefono_real` (string parcial)
- `nombre` (string parcial)
- `limit` (int 1..100, default 20)

### 4.2 Crear bot user

**Endpoint**

`POST /api/v1/bot-users`

**Body (JSON)**

```json
{
  "session_id": "5215559177781",
  "status": "new",
  "nombre": "Alfonso",
  "telefono_real": "5215559177781",
  "rol": "buyer",
  "bot_status": "free"
}
```

### 4.3 Obtener bot user por session_id

**Endpoint**

`GET /api/v1/bot-users/{session_id}`

### 4.4 Actualizar bot user

**Endpoint**

`PUT/PATCH /api/v1/bot-users/{session_id}`

### 4.5 Eliminar bot user

**Endpoint**

`DELETE /api/v1/bot-users/{session_id}`

### 4.6 Buscar bot user por session o teléfono

**Endpoint**

`GET /api/v1/bot-users/session/{sessionId}`

> Este endpoint intenta buscar primero por `session_id` exacto y, si no coincide, por `telefono_real` exacto.

---

## 5) Endpoints de Chat Histories (n8n)

### 5.1 Listar chat histories

**Endpoint**

`GET /api/v1/chat-histories`

**Query params opcionales**

- `session_id` (exacta)
- `limit` (int 1..100, default 20)

### 5.2 Crear chat history

**Endpoint**

`POST /api/v1/chat-histories`

**Body (JSON)**

```json
{
  "session_id": "5215559177781",
  "message": {
    "role": "user",
    "text": "Hola, quiero una casa en Puebla"
  }
}
```

### 5.3 Obtener chat history por ID

**Endpoint**

`GET /api/v1/chat-histories/{id}`

### 5.4 Actualizar chat history

**Endpoint**

`PUT/PATCH /api/v1/chat-histories/{id}`

### 5.5 Eliminar chat history

**Endpoint**

`DELETE /api/v1/chat-histories/{id}`

---

## 6) Ejemplos prácticos (curl)

> Recuerda enviar autenticación (`Authorization: Bearer ...` o `X-Api-Key: ...`).

### Bot Users

```bash
# Crear
curl -X POST "https://tu-dominio.com/api/v1/bot-users" \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "session_id": "5215559177781",
    "status": "new",
    "nombre": "Alfonso",
    "telefono_real": "5215559177781",
    "rol": "buyer"
  }'

# Listar
curl "https://tu-dominio.com/api/v1/bot-users?status=new&limit=10" \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json"

# Ver uno
curl "https://tu-dominio.com/api/v1/bot-users/5215559177781" \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json"

# Buscar por session_id o telefono_real
curl "https://tu-dominio.com/api/v1/bot-users/session/5215559177781" \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json"

# Actualizar
curl -X PATCH "https://tu-dominio.com/api/v1/bot-users/5215559177781" \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"status":"active","bot_status":"busy"}'

# Eliminar
curl -X DELETE "https://tu-dominio.com/api/v1/bot-users/5215559177781" \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json"
```

### Chat Histories

```bash
# Crear
curl -X POST "https://tu-dominio.com/api/v1/chat-histories" \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "session_id": "5215559177781",
    "message": {
      "role": "user",
      "text": "Hola, quiero una casa en Puebla"
    }
  }'

# Listar por sesión
curl "https://tu-dominio.com/api/v1/chat-histories?session_id=5215559177781" \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json"

# Ver uno
curl "https://tu-dominio.com/api/v1/chat-histories/1" \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json"

# Actualizar
curl -X PATCH "https://tu-dominio.com/api/v1/chat-histories/1" \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "message": {
      "role": "assistant",
      "text": "Te comparto opciones disponibles"
    }
  }'

# Eliminar
curl -X DELETE "https://tu-dominio.com/api/v1/chat-histories/1" \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json"
```
