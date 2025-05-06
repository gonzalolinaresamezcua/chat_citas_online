<?php
/**
 * API para gestión de citas
 * Maneja creación, edición, eliminación y consulta de citas
 */

define('ACCESO_PERMITIDO', true);

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/openai.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

switch ($method) {
    case 'GET':
        $sesion = verificar_sesion();
        if (!$sesion['logged_in']) {
            json_response(['success' => false, 'message' => 'Acceso denegado'], 403);
        }
        
        $citas = obtener_citas();
        
        if ($action === 'available_slots') {
            $servicio_id = isset($_GET['servicio_id']) ? (int)$_GET['servicio_id'] : 0;
            $fecha = isset($_GET['fecha']) ? sanitizar_input($_GET['fecha']) : '';
            
            if ($servicio_id <= 0 || empty($fecha)) {
                json_response(['success' => false, 'message' => 'Parámetros inválidos'], 400);
            }
            
            $horarios = obtener_horarios_disponibles($fecha, $servicio_id);
            json_response(['success' => true, 'slots' => $horarios]);
        } elseif ($id > 0) {
            $cita = null;
            foreach ($citas as $c) {
                if ($c['id'] == $id) {
                    if (es_administrador() || $c['usuario_id'] == $sesion['user']['id']) {
                        $cita = $c;
                    }
                    break;
                }
            }
            
            if ($cita) {
                json_response(['success' => true, 'cita' => $cita]);
            } else {
                json_response(['success' => false, 'message' => 'Cita no encontrada o acceso denegado'], 404);
            }
        } else {
            $citas_filtradas = [];
            
            if (es_administrador()) {
                $citas_filtradas = $citas;
            } else {
                foreach ($citas as $cita) {
                    if ($cita['usuario_id'] == $sesion['user']['id']) {
                        $citas_filtradas[] = $cita;
                    }
                }
            }
            
            json_response(['success' => true, 'citas' => $citas_filtradas]);
        }
        break;
        
    case 'POST':
        $sesion = verificar_sesion();
        if (!$sesion['logged_in']) {
            json_response(['success' => false, 'message' => 'Acceso denegado'], 403);
        }
        
        if (!isset($_POST['csrf_token']) || !verificar_token_csrf($_POST['csrf_token'])) {
            json_response(['success' => false, 'message' => 'Token CSRF inválido'], 403);
        }
        
        if ($action === 'generate_confirmation') {
            $servicio = isset($_POST['servicio']) ? sanitizar_input($_POST['servicio']) : '';
            $fecha = isset($_POST['fecha']) ? sanitizar_input($_POST['fecha']) : '';
            $hora = isset($_POST['hora']) ? sanitizar_input($_POST['hora']) : '';
            
            if (empty($servicio) || empty($fecha) || empty($hora)) {
                json_response(['success' => false, 'message' => 'Parámetros inválidos'], 400);
            }
            
            $resultado = generar_mensaje_confirmacion($servicio, $fecha, $hora);
            
            if ($resultado['success']) {
                json_response(['success' => true, 'message' => $resultado['text']]);
            } else {
                json_response(['success' => false, 'message' => $resultado['message']], 500);
            }
        } else {
            $servicio_id = isset($_POST['servicio_id']) ? (int)$_POST['servicio_id'] : 0;
            $fecha_str = isset($_POST['fecha']) ? sanitizar_input($_POST['fecha']) : '';
            $hora = isset($_POST['hora']) ? sanitizar_input($_POST['hora']) : '';
            $mensaje = isset($_POST['mensaje']) ? sanitizar_input($_POST['mensaje']) : '';
            
            $errores = [];
            
            if ($servicio_id <= 0) {
                $errores['servicio_id'] = 'Debes seleccionar un servicio';
            }
            
            if (empty($fecha_str)) {
                $errores['fecha'] = 'Debes seleccionar una fecha';
            }
            
            if (empty($hora)) {
                $errores['hora'] = 'Debes seleccionar una hora';
            }
            
            if (!empty($errores)) {
                json_response(['success' => false, 'message' => 'Hay errores en el formulario', 'errors' => $errores], 400);
            }
            
            $fecha = $fecha_str . ' ' . $hora . ':00';
            
            $servicios = obtener_servicios();
            $servicio = null;
            foreach ($servicios as $s) {
                if ($s['id'] == $servicio_id) {
                    $servicio = $s;
                    break;
                }
            }
            
            if (!$servicio) {
                json_response(['success' => false, 'message' => 'El servicio seleccionado no existe'], 400);
            }
            
            if (!verificar_disponibilidad($fecha_str, $hora, $servicio['duracion'], $servicio_id)) {
                json_response(['success' => false, 'message' => 'El horario seleccionado no está disponible'], 400);
            }
            
            if (empty($mensaje)) {
                $resultado = generar_mensaje_confirmacion($servicio['nombre'], $fecha_str, $hora);
                if ($resultado['success']) {
                    $mensaje = $resultado['text'];
                } else {
                    $mensaje = "Tu cita para {$servicio['nombre']} está programada el " . formatear_fecha($fecha) . ". ¡Gracias!";
                }
            }
            
            $citas = obtener_citas();
            
            $nuevo_id = generar_id($citas);
            
            $nueva_cita = [
                'id' => $nuevo_id,
                'usuario_id' => $sesion['user']['id'],
                'servicio_id' => $servicio_id,
                'fecha' => $fecha,
                'estado' => 'pendiente',
                'mensaje' => $mensaje
            ];
            
            $citas[] = $nueva_cita;
            if (guardar_citas($citas)) {
                json_response([
                    'success' => true, 
                    'message' => 'Cita creada correctamente',
                    'cita_id' => $nuevo_id,
                    'cita' => $nueva_cita
                ], 201);
            } else {
                json_response(['success' => false, 'message' => 'Error al guardar la cita'], 500);
            }
        }
        break;
        
    case 'PUT':
        $sesion = verificar_sesion();
        if (!$sesion['logged_in']) {
            json_response(['success' => false, 'message' => 'Acceso denegado'], 403);
        }
        
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID de cita no válido'], 400);
        }
        
        parse_str(file_get_contents('php://input'), $put_vars);
        
        if (!isset($put_vars['csrf_token']) || !verificar_token_csrf($put_vars['csrf_token'])) {
            json_response(['success' => false, 'message' => 'Token CSRF inválido'], 403);
        }
        
        $citas = obtener_citas();
        
        $cita_encontrada = false;
        foreach ($citas as $key => $cita) {
            if ($cita['id'] == $id) {
                if (es_administrador() || $cita['usuario_id'] == $sesion['user']['id']) {
                    $cita_encontrada = true;
                    
                    if ($action === 'update_status') {
                        if (!es_administrador()) {
                            json_response(['success' => false, 'message' => 'No tienes permisos para cambiar el estado de la cita'], 403);
                        }
                        
                        $estado = isset($put_vars['estado']) ? sanitizar_input($put_vars['estado']) : '';
                        if (!in_array($estado, ['pendiente', 'confirmada', 'rechazada'])) {
                            json_response(['success' => false, 'message' => 'Estado no válido'], 400);
                        }
                        
                        $citas[$key]['estado'] = $estado;
                    } else {
                        json_response(['success' => false, 'message' => 'Operación no soportada'], 400);
                    }
                }
                break;
            }
        }
        
        if (!$cita_encontrada) {
            json_response(['success' => false, 'message' => 'Cita no encontrada o acceso denegado'], 404);
        }
        
        if (guardar_citas($citas)) {
            json_response(['success' => true, 'message' => 'Cita actualizada correctamente']);
        } else {
            json_response(['success' => false, 'message' => 'Error al guardar la cita'], 500);
        }
        break;
        
    case 'DELETE':
        $sesion = verificar_sesion();
        if (!$sesion['logged_in']) {
            json_response(['success' => false, 'message' => 'Acceso denegado'], 403);
        }
        
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID de cita no válido'], 400);
        }
        
        parse_str(file_get_contents('php://input'), $delete_vars);
        
        if (!isset($delete_vars['csrf_token']) || !verificar_token_csrf($delete_vars['csrf_token'])) {
            json_response(['success' => false, 'message' => 'Token CSRF inválido'], 403);
        }
        
        $citas = obtener_citas();
        
        $cita_encontrada = false;
        foreach ($citas as $key => $cita) {
            if ($cita['id'] == $id) {
                if (es_administrador() || $cita['usuario_id'] == $sesion['user']['id']) {
                    unset($citas[$key]);
                    $cita_encontrada = true;
                }
                break;
            }
        }
        
        if (!$cita_encontrada) {
            json_response(['success' => false, 'message' => 'Cita no encontrada o acceso denegado'], 404);
        }
        
        $citas = array_values($citas);
        
        if (guardar_citas($citas)) {
            json_response(['success' => true, 'message' => 'Cita eliminada correctamente']);
        } else {
            json_response(['success' => false, 'message' => 'Error al eliminar la cita'], 500);
        }
        break;
        
    default:
        json_response(['success' => false, 'message' => 'Método no permitido'], 405);
        break;
}
