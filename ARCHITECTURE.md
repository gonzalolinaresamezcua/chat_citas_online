# Arquitectura del Sistema de Control de Citas Online

## Estructura de Directorios

```
chat_citas_online/
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   ├── flatpickr.min.css
│   │   └── styles.css
│   ├── js/
│   │   ├── bootstrap.bundle.min.js
│   │   ├── flatpickr.min.js
│   │   ├── jquery.min.js
│   │   ├── auth.js
│   │   ├── admin.js
│   │   └── cliente.js
│   └── img/
├── data/
│   ├── usuarios.json
│   ├── servicios.json
│   └── citas.json
├── includes/
│   ├── config.php
│   ├── auth.php
│   ├── openai.php
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── admin/
│   ├── index.php
│   ├── servicios.php
│   └── citas.php
├── cliente/
│   ├── index.php
│   ├── reservar.php
│   └── mis-citas.php
├── api/
│   ├── auth.php
│   ├── servicios.php
│   └── citas.php
├── .env
├── index.php
└── README.md
```

## Modelos de Datos

### usuarios.json
```json
[
  {
    "id": 1,
    "nombre": "Admin",
    "email": "admin@example.com",
    "password": "$2y$10$...",
    "rol": "admin",
    "fecha_registro": "2023-05-01 10:00:00"
  },
  {
    "id": 2,
    "nombre": "Cliente Ejemplo",
    "email": "cliente@example.com",
    "password": "$2y$10$...",
    "rol": "cliente",
    "fecha_registro": "2023-05-02 11:30:00"
  }
]
```

### servicios.json
```json
[
  {
    "id": 1,
    "nombre": "Corte de Cabello",
    "descripcion": "Servicio profesional de corte de cabello...",
    "duracion": 30
  },
  {
    "id": 2,
    "nombre": "Masaje Relajante",
    "descripcion": "Masaje corporal completo para aliviar tensiones...",
    "duracion": 60
  }
]
```

### citas.json
```json
[
  {
    "id": 1,
    "usuario_id": 2,
    "servicio_id": 1,
    "fecha": "2023-05-10 14:00:00",
    "estado": "confirmada",
    "mensaje": "Tu cita para Corte de Cabello está programada el 10/05/2023 a las 14:00. ¡Gracias!"
  },
  {
    "id": 2,
    "usuario_id": 2,
    "servicio_id": 2,
    "fecha": "2023-05-15 16:30:00",
    "estado": "pendiente",
    "mensaje": "Tu cita para Masaje Relajante está programada el 15/05/2023 a las 16:30. ¡Gracias!"
  }
]
```

## Endpoints de API

### Autenticación (api/auth.php)
- `POST /api/auth.php?action=register` - Registrar nuevo usuario
  - Parámetros: nombre, email, password
  - Respuesta: {success: true/false, message: "...", user_id: X}

- `POST /api/auth.php?action=login` - Iniciar sesión
  - Parámetros: email, password
  - Respuesta: {success: true/false, message: "...", user: {...}}

- `GET /api/auth.php?action=logout` - Cerrar sesión
  - Respuesta: {success: true/false, message: "..."}

- `GET /api/auth.php?action=check` - Verificar sesión activa
  - Respuesta: {logged_in: true/false, user: {...}}

### Servicios (api/servicios.php)
- `GET /api/servicios.php` - Obtener todos los servicios
  - Respuesta: {success: true/false, servicios: [...]}

- `GET /api/servicios.php?id=X` - Obtener un servicio específico
  - Respuesta: {success: true/false, servicio: {...}}

- `POST /api/servicios.php` - Crear nuevo servicio (solo admin)
  - Parámetros: nombre, descripcion, duracion
  - Respuesta: {success: true/false, message: "...", servicio_id: X}

- `PUT /api/servicios.php?id=X` - Actualizar servicio (solo admin)
  - Parámetros: nombre, descripcion, duracion
  - Respuesta: {success: true/false, message: "..."}

- `DELETE /api/servicios.php?id=X` - Eliminar servicio (solo admin)
  - Respuesta: {success: true/false, message: "..."}

- `POST /api/servicios.php?action=generate_description` - Generar descripción con OpenAI
  - Parámetros: keywords
  - Respuesta: {success: true/false, description: "..."}

### Citas (api/citas.php)
- `GET /api/citas.php` - Obtener todas las citas (admin) o citas del usuario (cliente)
  - Respuesta: {success: true/false, citas: [...]}

- `GET /api/citas.php?id=X` - Obtener una cita específica
  - Respuesta: {success: true/false, cita: {...}}

- `POST /api/citas.php` - Crear nueva cita
  - Parámetros: servicio_id, fecha
  - Respuesta: {success: true/false, message: "...", cita_id: X}

- `PUT /api/citas.php?id=X&action=update_status` - Actualizar estado de cita (solo admin)
  - Parámetros: estado (confirmada/rechazada/pendiente)
  - Respuesta: {success: true/false, message: "..."}

- `DELETE /api/citas.php?id=X` - Cancelar cita
  - Respuesta: {success: true/false, message: "..."}

- `GET /api/citas.php?action=available_slots&servicio_id=X&fecha=Y` - Obtener horarios disponibles
  - Respuesta: {success: true/false, slots: [...]}

- `POST /api/citas.php?action=generate_confirmation` - Generar mensaje de confirmación con OpenAI
  - Parámetros: servicio, fecha, hora
  - Respuesta: {success: true/false, message: "..."}

## Flujo de Autenticación

1. El usuario accede a la página de inicio (`index.php`)
2. Si no tiene sesión activa, se muestra el formulario de login/registro
3. Al enviar el formulario, se realiza una petición AJAX a `api/auth.php`
4. Si la autenticación es exitosa, se crea una sesión PHP y se redirige según el rol:
   - Administrador: `admin/index.php`
   - Cliente: `cliente/index.php`
5. Todas las páginas protegidas verifican la sesión activa y el rol del usuario
6. El cierre de sesión destruye la sesión PHP y redirige a la página de inicio

## Integración con OpenAI

### Generación de Descripciones de Servicios
1. El administrador proporciona palabras clave para el servicio
2. Se realiza una petición a la API de OpenAI con un prompt estructurado
3. La respuesta se procesa y se muestra como sugerencia de descripción
4. El administrador puede editar la descripción antes de guardarla

### Generación de Mensajes de Confirmación
1. Al crear una cita, se recopilan los datos del servicio, fecha y hora
2. Se realiza una petición a la API de OpenAI con un prompt estructurado
3. La respuesta se procesa y se guarda como mensaje de confirmación
4. El mensaje se muestra al cliente y se incluye en los detalles de la cita

## Seguridad

### Protección CSRF
- Generación de tokens CSRF para cada formulario
- Validación del token en cada petición POST/PUT/DELETE

### Validación de Sesiones
- Verificación de sesión activa en cada página protegida
- Verificación de rol de usuario para acciones restringidas

### Límites de Tasa para OpenAI
- Implementación de un sistema de caché para reducir llamadas repetidas
- Limitación de número de solicitudes por usuario/sesión
- Registro de uso de la API para monitoreo

## Tecnologías Utilizadas

- **Backend**: PHP 7.4+
- **Frontend**: HTML5, JavaScript, jQuery, AJAX
- **Estilos**: Bootstrap 5
- **Calendario**: Flatpickr
- **API Externa**: OpenAI (modelo GPT-4.1-Nano)
- **Almacenamiento**: Archivos JSON
- **Seguridad**: password_hash, tokens CSRF, validación de sesiones
