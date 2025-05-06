<?php
/**
 * Script de prueba para verificar la funcionalidad del sistema
 * Este script realiza pruebas automatizadas de las principales funcionalidades
 */

define('ACCESO_PERMITIDO', true);
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/openai.php';

function mostrar_resultado($prueba, $resultado, $mensaje = '') {
    echo "<div style='margin-bottom: 10px; padding: 10px; border-radius: 5px; " . 
         "background-color: " . ($resultado ? "#d4edda" : "#f8d7da") . "; " .
         "color: " . ($resultado ? "#155724" : "#721c24") . ";'>" .
         "<strong>" . ($resultado ? "✅ ÉXITO" : "❌ ERROR") . ":</strong> " .
         "$prueba - $mensaje</div>";
}

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Pruebas del Sistema de Citas</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
    <div class='container my-5'>
        <h1 class='mb-4'>Pruebas del Sistema de Citas Online</h1>";

echo "<h2 class='mt-4'>1. Prueba de acceso a archivos JSON</h2>";

$usuarios_accesible = file_exists(DATA_PATH . '/usuarios.json');
mostrar_resultado("Acceso a usuarios.json", $usuarios_accesible, 
                 $usuarios_accesible ? "El archivo es accesible" : "No se puede acceder al archivo");

$servicios_accesible = file_exists(DATA_PATH . '/servicios.json');
mostrar_resultado("Acceso a servicios.json", $servicios_accesible, 
                 $servicios_accesible ? "El archivo es accesible" : "No se puede acceder al archivo");

$citas_accesible = file_exists(DATA_PATH . '/citas.json');
mostrar_resultado("Acceso a citas.json", $citas_accesible, 
                 $citas_accesible ? "El archivo es accesible" : "No se puede acceder al archivo");

echo "<h2 class='mt-4'>2. Prueba de funciones de autenticación</h2>";

$password = "contraseña123";
$hash = password_hash($password, PASSWORD_DEFAULT);
$verificacion = password_verify($password, $hash);
mostrar_resultado("Hash de contraseña", $verificacion, 
                 $verificacion ? "La función de hash funciona correctamente" : "Error en la función de hash");

$token = generar_token_csrf();
$verificacion_token = verificar_token_csrf($token);
mostrar_resultado("Token CSRF", $verificacion_token, 
                 $verificacion_token ? "La generación y verificación de tokens funciona correctamente" : "Error en la gestión de tokens CSRF");

echo "<h2 class='mt-4'>3. Prueba de funciones de gestión de datos</h2>";

$servicios = obtener_servicios();
$servicios_ok = is_array($servicios);
mostrar_resultado("Obtener servicios", $servicios_ok, 
                 $servicios_ok ? "Se obtuvieron " . count($servicios) . " servicios" : "Error al obtener servicios");

$citas = obtener_citas();
$citas_ok = is_array($citas);
mostrar_resultado("Obtener citas", $citas_ok, 
                 $citas_ok ? "Se obtuvieron " . count($citas) . " citas" : "Error al obtener citas");

echo "<h2 class='mt-4'>4. Prueba de funciones de utilidad</h2>";

$input_peligroso = "<script>alert('XSS')</script>";
$input_sanitizado = sanitizar_input($input_peligroso);
$sanitizacion_ok = $input_sanitizado !== $input_peligroso && strpos($input_sanitizado, "&lt;") !== false;
mostrar_resultado("Sanitización de input", $sanitizacion_ok, 
                 $sanitizacion_ok ? "La sanitización funciona correctamente" : "Error en la sanitización");

$fecha = "2023-05-15 14:30:00";
$fecha_formateada = formatear_fecha($fecha);
$formateo_ok = !empty($fecha_formateada) && $fecha_formateada !== $fecha;
mostrar_resultado("Formateo de fecha", $formateo_ok, 
                 $formateo_ok ? "Fecha formateada: $fecha_formateada" : "Error en el formateo de fecha");

echo "<h2 class='mt-4'>5. Prueba de integración con OpenAI</h2>";

$openai_configurado = defined('OPENAI_API_KEY') && !empty(OPENAI_API_KEY) && OPENAI_API_KEY !== 'tu_clave_api_aqui';
mostrar_resultado("Configuración de OpenAI", $openai_configurado, 
                 $openai_configurado ? "La API key está configurada" : "La API key no está configurada correctamente");

$rate_limit_ok = defined('OPENAI_RATE_LIMIT') && OPENAI_RATE_LIMIT > 0;
mostrar_resultado("Rate limiting", $rate_limit_ok, 
                 $rate_limit_ok ? "El rate limiting está configurado (límite: " . OPENAI_RATE_LIMIT . ")" : "El rate limiting no está configurado correctamente");

echo "<h2 class='mt-4'>6. Prueba de verificación de disponibilidad</h2>";

$fecha_prueba = date('Y-m-d', strtotime('+1 day'));
$hora_prueba = '10:00';
$duracion_prueba = 30;
$servicio_id_prueba = 1;

$disponibilidad = verificar_disponibilidad($fecha_prueba, $hora_prueba, $duracion_prueba, $servicio_id_prueba);
mostrar_resultado("Verificación de disponibilidad", true, 
                 "La función de verificación de disponibilidad se ejecutó sin errores (resultado: " . ($disponibilidad ? "disponible" : "no disponible") . ")");

$horarios = obtener_horarios_disponibles($fecha_prueba, $servicio_id_prueba);
$horarios_ok = is_array($horarios);
mostrar_resultado("Obtención de horarios disponibles", $horarios_ok, 
                 $horarios_ok ? "Se obtuvieron " . count($horarios) . " horarios disponibles" : "Error al obtener horarios disponibles");

echo "
        <div class='alert alert-info mt-4'>
            <h4>Instrucciones para pruebas manuales:</h4>
            <ol>
                <li>Registra un usuario administrador y un usuario cliente</li>
                <li>Inicia sesión como administrador y crea algunos servicios</li>
                <li>Prueba la generación de descripciones con OpenAI</li>
                <li>Inicia sesión como cliente y reserva algunas citas</li>
                <li>Verifica que las citas aparezcan en el panel de administrador</li>
                <li>Prueba aprobar/rechazar citas como administrador</li>
                <li>Verifica que los cambios se reflejen en el panel del cliente</li>
            </ol>
        </div>
        
        <div class='mt-4'>
            <a href='index.php' class='btn btn-primary'>Volver al inicio</a>
        </div>
    </div>
</body>
</html>";
?>
