# Checklist de Pruebas Manuales

Este documento proporciona una lista de verificación para probar manualmente todas las funcionalidades del sistema de citas online.

## 1. Autenticación de Usuarios

### Registro de Usuario
- [ ] Verificar que el formulario de registro valide en tiempo real con AJAX
- [ ] Comprobar que se requieran todos los campos obligatorios
- [ ] Verificar que se valide el formato de email
- [ ] Comprobar que las contraseñas coincidan
- [ ] Verificar que se muestre un mensaje de éxito al registrarse
- [ ] Comprobar que se cree el usuario en usuarios.json con contraseña encriptada

### Inicio de Sesión
- [ ] Verificar que el formulario de inicio de sesión valide en tiempo real con AJAX
- [ ] Comprobar que se requieran todos los campos obligatorios
- [ ] Verificar que se muestre un mensaje de error con credenciales incorrectas
- [ ] Comprobar que se inicie sesión correctamente con credenciales válidas
- [ ] Verificar que se redirija al panel correspondiente según el rol (admin/cliente)

### Cierre de Sesión
- [ ] Verificar que se cierre la sesión correctamente
- [ ] Comprobar que se redirija a la página de inicio

## 2. Interfaz de Administrador

### Panel de Administración
- [ ] Verificar que solo los usuarios con rol "admin" puedan acceder
- [ ] Comprobar que se muestren las estadísticas correctamente
- [ ] Verificar que se listen las citas recientes

### Gestión de Servicios
- [ ] Verificar que se listen todos los servicios
- [ ] Comprobar que se pueda agregar un nuevo servicio
- [ ] Verificar que se genere una descripción con OpenAI
- [ ] Comprobar que se pueda editar un servicio existente
- [ ] Verificar que se pueda eliminar un servicio

### Gestión de Citas
- [ ] Verificar que se listen todas las citas
- [ ] Comprobar que se puedan filtrar por estado
- [ ] Verificar que se pueda ver el detalle de una cita
- [ ] Comprobar que se pueda aprobar una cita pendiente
- [ ] Verificar que se pueda rechazar una cita pendiente
- [ ] Comprobar que se pueda eliminar una cita

## 3. Interfaz de Cliente

### Panel de Cliente
- [ ] Verificar que solo los usuarios con rol "cliente" puedan acceder
- [ ] Comprobar que se muestren las estadísticas correctamente
- [ ] Verificar que se listen las citas recientes

### Reserva de Citas
- [ ] Verificar que se muestre el calendario interactivo
- [ ] Comprobar que se carguen los servicios disponibles
- [ ] Verificar que al seleccionar un servicio se muestren los horarios disponibles
- [ ] Comprobar que se actualicen los horarios en tiempo real con AJAX
- [ ] Verificar que se valide la disponibilidad en el backend
- [ ] Comprobar que se genere un mensaje de confirmación con OpenAI
- [ ] Verificar que se cree la cita en citas.json

### Gestión de Citas del Cliente
- [ ] Verificar que se listen todas las citas del cliente
- [ ] Comprobar que se puedan filtrar por estado
- [ ] Verificar que se pueda ver el detalle de una cita
- [ ] Comprobar que se pueda cancelar una cita pendiente

## 4. Diseño Responsivo

- [ ] Verificar que la página de inicio se vea correctamente en dispositivos móviles
- [ ] Comprobar que el menú de navegación se colapse en dispositivos móviles
- [ ] Verificar que los formularios se adapten a diferentes tamaños de pantalla
- [ ] Comprobar que las tablas sean responsivas
- [ ] Verificar que el calendario se adapte a dispositivos móviles

## 5. Seguridad

- [ ] Verificar que las rutas protegidas requieran sesión activa
- [ ] Comprobar que se valide el rol del usuario para acceder a rutas específicas
- [ ] Verificar que los formularios incluyan tokens CSRF
- [ ] Comprobar que se validen los tokens CSRF en las solicitudes POST
- [ ] Verificar que se aplique rate limiting en las llamadas a la API de OpenAI
- [ ] Comprobar que los inputs estén sanitizados para prevenir XSS

## 6. Almacenamiento y Sincronización

- [ ] Verificar que los datos se guarden correctamente en los archivos JSON
- [ ] Comprobar que se actualicen las listas de citas sin recargar la página
- [ ] Verificar que los cambios en los datos se reflejen en tiempo real
