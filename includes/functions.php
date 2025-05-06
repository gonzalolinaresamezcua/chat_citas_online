<?php
/**
 * Funciones de utilidad general para el sistema
 */

if (!defined('ACCESO_PERMITIDO')) {
    die('Acceso no autorizado');
}

/**
 * Sanitiza una entrada para prevenir XSS
 * @param string $input Texto a sanitizar
 * @return string Texto sanitizado
 */
function sanitizar_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Genera una respuesta JSON y termina la ejecución
 * @param array $data Datos a enviar
 * @param int $status_code Código de estado HTTP
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Verifica si una solicitud es AJAX
 * @return bool True si es AJAX, false en caso contrario
 */
function es_ajax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Redirige a una URL
 * @param string $url URL de destino
 */
function redirigir($url) {
    header("Location: $url");
    exit;
}

/**
 * Genera un mensaje de alerta para mostrar en la interfaz
 * @param string $mensaje Texto del mensaje
 * @param string $tipo Tipo de alerta (success, danger, warning, info)
 * @return string HTML del mensaje
 */
function alerta($mensaje, $tipo = 'info') {
    return '<div class="alert alert-' . $tipo . ' alert-dismissible fade show" role="alert">
                ' . $mensaje . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>';
}

/**
 * Formatea una fecha para mostrar
 * @param string $fecha Fecha en formato Y-m-d H:i:s
 * @param bool $incluir_hora Si se debe incluir la hora
 * @return string Fecha formateada
 */
function formatear_fecha($fecha, $incluir_hora = true) {
    $timestamp = strtotime($fecha);
    if ($incluir_hora) {
        return date('d/m/Y H:i', $timestamp);
    }
    return date('d/m/Y', $timestamp);
}

/**
 * Genera un ID único para un nuevo elemento
 * @param array $elementos Lista de elementos existentes
 * @return int Nuevo ID
 */
function generar_id($elementos) {
    if (empty($elementos)) {
        return 1;
    }
    $ids = array_column($elementos, 'id');
    return max($ids) + 1;
}

/**
 * Verifica si una fecha y hora están disponibles para citas
 * @param string $fecha Fecha en formato Y-m-d
 * @param string $hora Hora en formato H:i
 * @param int $duracion Duración del servicio en minutos
 * @param int $servicio_id ID del servicio
 * @param array $citas_existentes Lista de citas existentes
 * @return bool True si está disponible, false en caso contrario
 */
function verificar_disponibilidad($fecha, $hora, $duracion, $servicio_id, $citas_existentes = null) {
    if ($citas_existentes === null) {
        $ruta_archivo = DATA_PATH . '/citas.json';
        if (file_exists($ruta_archivo)) {
            $json = file_get_contents($ruta_archivo);
            $citas_existentes = json_decode($json, true) ?: [];
        } else {
            $citas_existentes = [];
        }
    }
    
    $inicio_cita = strtotime("$fecha $hora");
    $fin_cita = $inicio_cita + ($duracion * 60);
    
    $hora_inicio = (int)date('H', $inicio_cita);
    $hora_fin = (int)date('H', $fin_cita);
    if ($hora_inicio < 9 || $hora_fin > 18) {
        return false;
    }
    
    $dia_semana = date('N', $inicio_cita);
    if ($dia_semana > 5) { // 6=sábado, 7=domingo
        return false;
    }
    
    foreach ($citas_existentes as $cita) {
        if ($cita['estado'] === 'rechazada') {
            continue;
        }
        
        $servicio_duracion = $duracion;
        if ($cita['servicio_id'] != $servicio_id) {
            $servicios = obtener_servicios();
            foreach ($servicios as $servicio) {
                if ($servicio['id'] == $cita['servicio_id']) {
                    $servicio_duracion = $servicio['duracion'];
                    break;
                }
            }
        }
        
        $inicio_existente = strtotime($cita['fecha']);
        $fin_existente = $inicio_existente + ($servicio_duracion * 60);
        
        if (
            ($inicio_cita >= $inicio_existente && $inicio_cita < $fin_existente) ||
            ($fin_cita > $inicio_existente && $fin_cita <= $fin_existente) ||
            ($inicio_cita <= $inicio_existente && $fin_cita >= $fin_existente)
        ) {
            return false;
        }
    }
    
    return true;
}

/**
 * Obtiene los horarios disponibles para un servicio en una fecha
 * @param string $fecha Fecha en formato Y-m-d
 * @param int $servicio_id ID del servicio
 * @return array Lista de horarios disponibles
 */
function obtener_horarios_disponibles($fecha, $servicio_id) {
    $duracion = 30; // Valor predeterminado
    $servicios = obtener_servicios();
    foreach ($servicios as $servicio) {
        if ($servicio['id'] == $servicio_id) {
            $duracion = $servicio['duracion'];
            break;
        }
    }
    
    $citas_existentes = obtener_citas();
    
    $horarios_disponibles = [];
    $hora_inicio = 9 * 60; // 9:00 en minutos
    $hora_fin = 18 * 60; // 18:00 en minutos
    $intervalo = 30; // Intervalo en minutos
    
    for ($minutos = $hora_inicio; $minutos < $hora_fin; $minutos += $intervalo) {
        $hora = sprintf('%02d:%02d', floor($minutos / 60), $minutos % 60);
        
        if (verificar_disponibilidad($fecha, $hora, $duracion, $servicio_id, $citas_existentes)) {
            $horarios_disponibles[] = $hora;
        }
    }
    
    return $horarios_disponibles;
}

/**
 * Obtiene todos los servicios del archivo JSON
 * @return array Lista de servicios
 */
function obtener_servicios() {
    $ruta_archivo = DATA_PATH . '/servicios.json';
    if (file_exists($ruta_archivo)) {
        $json = file_get_contents($ruta_archivo);
        return json_decode($json, true) ?: [];
    }
    return [];
}

/**
 * Guarda la lista de servicios en el archivo JSON
 * @param array $servicios Lista de servicios a guardar
 * @return bool Éxito de la operación
 */
function guardar_servicios($servicios) {
    $ruta_archivo = DATA_PATH . '/servicios.json';
    $json = json_encode($servicios, JSON_PRETTY_PRINT);
    return file_put_contents($ruta_archivo, $json) !== false;
}

/**
 * Obtiene todas las citas del archivo JSON
 * @return array Lista de citas
 */
function obtener_citas() {
    $ruta_archivo = DATA_PATH . '/citas.json';
    if (file_exists($ruta_archivo)) {
        $json = file_get_contents($ruta_archivo);
        return json_decode($json, true) ?: [];
    }
    return [];
}

/**
 * Guarda la lista de citas en el archivo JSON
 * @param array $citas Lista de citas a guardar
 * @return bool Éxito de la operación
 */
function guardar_citas($citas) {
    $ruta_archivo = DATA_PATH . '/citas.json';
    $json = json_encode($citas, JSON_PRETTY_PRINT);
    return file_put_contents($ruta_archivo, $json) !== false;
}
