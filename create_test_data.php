<?php
/**
 * Script para crear datos de prueba para el sistema de citas
 * Crea usuarios, servicios y citas de ejemplo para probar la funcionalidad
 */

define('ACCESO_PERMITIDO', true);
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/openai.php';

function mostrar_resultado($accion, $resultado, $mensaje = '') {
    echo "<div style='margin-bottom: 10px; padding: 10px; border-radius: 5px; " . 
         "background-color: " . ($resultado ? "#d4edda" : "#f8d7da") . "; " .
         "color: " . ($resultado ? "#155724" : "#721c24") . ";'>" .
         "<strong>" . ($resultado ? "✅ ÉXITO" : "❌ ERROR") . ":</strong> " .
         "$accion - $mensaje</div>";
}

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Creación de Datos de Prueba</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
    <div class='container my-5'>
        <h1 class='mb-4'>Creación de Datos de Prueba</h1>";

echo "<h2 class='mt-4'>1. Creando usuarios de prueba</h2>";

$usuarios_existentes = obtener_usuarios();

$admin_id = generar_id($usuarios_existentes);
$admin = [
    'id' => $admin_id,
    'nombre' => 'Administrador',
    'email' => 'admin@example.com',
    'password' => password_hash('admin123', PASSWORD_DEFAULT),
    'rol' => 'admin',
    'fecha_registro' => date('Y-m-d H:i:s')
];

$usuarios_actualizados = array_merge($usuarios_existentes, [$admin]);

$cliente_id = generar_id($usuarios_actualizados);
$cliente = [
    'id' => $cliente_id,
    'nombre' => 'Cliente Ejemplo',
    'email' => 'cliente@example.com',
    'password' => password_hash('cliente123', PASSWORD_DEFAULT),
    'rol' => 'cliente',
    'fecha_registro' => date('Y-m-d H:i:s')
];

$usuarios = [];
$usuarios[] = $admin;
$usuarios[] = $cliente;

$usuarios_json = json_encode($usuarios, JSON_PRETTY_PRINT);
$resultado_usuarios = file_put_contents(DATA_PATH . '/usuarios.json', $usuarios_json);

mostrar_resultado("Creación de usuarios", $resultado_usuarios !== false, 
                 $resultado_usuarios !== false ? "Se crearon 2 usuarios (admin y cliente)" : "Error al crear usuarios");

echo "<h2 class='mt-4'>2. Creando servicios de prueba</h2>";

$servicios_existentes = obtener_servicios();

$servicios = [];

$servicio1_id = generar_id($servicios_existentes);
$servicios[] = [
    'id' => $servicio1_id,
    'nombre' => 'Corte de Cabello',
    'descripcion' => 'Servicio profesional de corte de cabello con las últimas tendencias y estilos personalizados para cada cliente.',
    'duracion' => 30
];

$servicios_actualizados = array_merge($servicios_existentes, [$servicios[0]]);
$servicio2_id = generar_id($servicios_actualizados);
$servicios[] = [
    'id' => $servicio2_id,
    'nombre' => 'Masaje Terapéutico',
    'descripcion' => 'Masaje relajante que alivia tensiones musculares y proporciona bienestar general. Técnicas personalizadas según necesidades.',
    'duracion' => 60
];

$servicios_actualizados = array_merge($servicios_existentes, [$servicios[0], $servicios[1]]);
$servicio3_id = generar_id($servicios_actualizados);
$servicios[] = [
    'id' => $servicio3_id,
    'nombre' => 'Limpieza Facial',
    'descripcion' => 'Tratamiento facial completo que incluye limpieza profunda, exfoliación y mascarilla hidratante para una piel radiante.',
    'duracion' => 45
];

$servicios_json = json_encode($servicios, JSON_PRETTY_PRINT);
$resultado_servicios = file_put_contents(DATA_PATH . '/servicios.json', $servicios_json);

mostrar_resultado("Creación de servicios", $resultado_servicios !== false, 
                 $resultado_servicios !== false ? "Se crearon 3 servicios de ejemplo" : "Error al crear servicios");

echo "<h2 class='mt-4'>3. Creando citas de prueba</h2>";

$fecha_hoy = date('Y-m-d');
$fecha_manana = date('Y-m-d', strtotime('+1 day'));
$fecha_pasado = date('Y-m-d', strtotime('+2 days'));

$citas_existentes = obtener_citas();

$citas = [];

$cita1_id = generar_id($citas_existentes);
$citas[] = [
    'id' => $cita1_id,
    'usuario_id' => $cliente_id,
    'servicio_id' => $servicios[0]['id'],
    'fecha' => $fecha_hoy . ' 10:00:00',
    'estado' => 'confirmada',
    'mensaje' => 'Tu cita para Corte de Cabello está programada hoy a las 10:00. ¡Te esperamos!'
];

$citas_actualizadas = array_merge($citas_existentes, [$citas[0]]);
$cita2_id = generar_id($citas_actualizadas);
$citas[] = [
    'id' => $cita2_id,
    'usuario_id' => $cliente_id,
    'servicio_id' => $servicios[1]['id'],
    'fecha' => $fecha_manana . ' 15:30:00',
    'estado' => 'pendiente',
    'mensaje' => 'Tu cita para Masaje Terapéutico está pendiente de confirmación para mañana a las 15:30.'
];

$citas_actualizadas = array_merge($citas_existentes, [$citas[0], $citas[1]]);
$cita3_id = generar_id($citas_actualizadas);
$citas[] = [
    'id' => $cita3_id,
    'usuario_id' => $cliente_id,
    'servicio_id' => $servicios[2]['id'],
    'fecha' => $fecha_pasado . ' 11:15:00',
    'estado' => 'rechazada',
    'mensaje' => 'Lo sentimos, tu cita para Limpieza Facial no está disponible en el horario solicitado.'
];


$citas_json = json_encode($citas, JSON_PRETTY_PRINT);
$resultado_citas = file_put_contents(DATA_PATH . '/citas.json', $citas_json);

mostrar_resultado("Creación de citas", $resultado_citas !== false, 
                 $resultado_citas !== false ? "Se crearon 3 citas de ejemplo (confirmada, pendiente y rechazada)" : "Error al crear citas");

echo "
        <div class='alert alert-info mt-4'>
            <h4>Credenciales de prueba:</h4>
            <p><strong>Administrador:</strong> admin@example.com / admin123</p>
            <p><strong>Cliente:</strong> cliente@example.com / cliente123</p>
        </div>
        
        <div class='mt-4'>
            <a href='test.php' class='btn btn-primary'>Ejecutar pruebas</a>
            <a href='index.php' class='btn btn-secondary ms-2'>Ir al inicio</a>
        </div>
    </div>
</body>
</html>";
?>
