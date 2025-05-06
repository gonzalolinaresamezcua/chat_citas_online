# Sistema de Control de Citas Online

Este proyecto implementa un sistema de control de citas online utilizando PHP, HTML5, JavaScript (con AJAX) y Bootstrap 5.

## Características

### Autenticación de Usuarios
- Dos tipos de roles: Administrador y Cliente
- Formularios de registro/inicio de sesión con validación en tiempo real mediante AJAX
- Almacenamiento de usuarios en archivo usuarios.json con contraseñas encriptadas

### Interfaz Administrativa
- Panel para gestionar servicios (agregar/eliminar/editar)
- Panel para gestionar citas (ver, aprobar/rechazar o eliminar)
- Integración con OpenAI API para generar descripciones automáticas de servicios

### Interfaz de Cliente
- Calendario interactivo para seleccionar fecha/hora disponibles
- Sistema de reservas en tiempo real con AJAX
- Confirmación de cita con mensaje generado por OpenAI

### Almacenamiento y Sincronización
- Datos guardados en archivos JSON (citas.json, servicios.json)
- Uso de AJAX para actualizar dinámicamente listas de citas

### Diseño Responsivo
- Plantilla Bootstrap 5 con menú de navegación, alertas estiladas y formularios accesibles
- Página de inicio pública con formulario de registro/inicio de sesión

### Seguridad
- Validación de sesiones activas
- Protección contra ataques CSRF
- Límites de tasa en llamadas a la API de OpenAI

## Estructura del Proyecto
```
chat_citas_online/
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
├── data/
│   ├── usuarios.json
│   ├── servicios.json
│   └── citas.json
├── includes/
│   ├── config.php
│   ├── auth.php
│   ├── openai.php
│   └── functions.php
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

## Requisitos
- PHP 7.4 o superior
- Acceso a la API de OpenAI (clave API)
- Navegador moderno con soporte para JavaScript y AJAX

## Instalación
1. Clonar el repositorio
2. Crear un archivo .env con la clave API de OpenAI
3. Asegurarse de que los directorios de datos tengan permisos de escritura
4. Acceder a la aplicación a través de un servidor web con soporte para PHP
