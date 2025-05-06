<?php
/**
 * API para gestión de servicios
 * Maneja creación, edición, eliminación y consulta de servicios
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
        $servicios = obtener_servicios();
        
        if ($id > 0) {
            $servicio = null;
            foreach ($servicios as $s) {
                if ($s['id'] == $id) {
                    $servicio = $s;
                    break;
                }
            }
            
            if ($servicio) {
                json_response(['success' => true, 'servicio' => $servicio]);
            } else {
                json_response(['success' => false, 'message' => 'Servicio no encontrado'], 404);
            }
        } else {
            json_response(['success' => true, 'servicios' => $servicios]);
        }
        break;
        
    case 'POST':
        $sesion = verificar_sesion();
        if (!$sesion['logged_in'] || !es_administrador()) {
            json_response(['success' => false, 'message' => 'Acceso denegado'], 403);
        }
        
        if (!isset($_POST['csrf_token']) || !verificar_token_csrf($_POST['csrf_token'])) {
            json_response(['success' => false, 'message' => 'Token CSRF inválido'], 403);
        }
        
        if ($action === 'generate_description') {
            $keywords = isset($_POST['keywords']) ? sanitizar_input($_POST['keywords']) : '';
            
            if (empty($keywords)) {
                json_response(['success' => false, 'message' => 'Debes proporcionar palabras clave'], 400);
            }
            
            $resultado = generar_descripcion_servicio($keywords);
            
            if ($resultado['success']) {
                json_response(['success' => true, 'text' => $resultado['text']]);
            } else {
                json_response(['success' => false, 'message' => $resultado['message']], 500);
            }
        } else {
            $nombre = isset($_POST['nombre']) ? sanitizar_input($_POST['nombre']) : '';
            $descripcion = isset($_POST['descripcion']) ? sanitizar_input($_POST['descripcion']) : '';
            $duracion = isset($_POST['duracion']) ? (int)$_POST['duracion'] : 0;
            
            $errores = [];
            
            if (empty($nombre)) {
                $errores['nombre'] = 'El nombre es obligatorio';
            }
            
            if (empty($descripcion)) {
                $errores['descripcion'] = 'La descripción es obligatoria';
            }
            
            if ($duracion <= 0) {
                $errores['duracion'] = 'La duración debe ser mayor a 0 minutos';
            }
            
            if (!empty($errores)) {
                json_response(['success' => false, 'message' => 'Hay errores en el formulario', 'errors' => $errores], 400);
            }
            
            $servicios = obtener_servicios();
            
            $nuevo_id = generar_id($servicios);
            
            $nuevo_servicio = [
                'id' => $nuevo_id,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'duracion' => $duracion
            ];
            
            $servicios[] = $nuevo_servicio;
            if (guardar_servicios($servicios)) {
                json_response([
                    'success' => true, 
                    'message' => 'Servicio creado correctamente',
                    'servicio_id' => $nuevo_id,
                    'servicio' => $nuevo_servicio
                ], 201);
            } else {
                json_response(['success' => false, 'message' => 'Error al guardar el servicio'], 500);
            }
        }
        break;
        
    case 'PUT':
        $sesion = verificar_sesion();
        if (!$sesion['logged_in'] || !es_administrador()) {
            json_response(['success' => false, 'message' => 'Acceso denegado'], 403);
        }
        
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID de servicio no válido'], 400);
        }
        
        parse_str(file_get_contents('php://input'), $put_vars);
        
        if (!isset($put_vars['csrf_token']) || !verificar_token_csrf($put_vars['csrf_token'])) {
            json_response(['success' => false, 'message' => 'Token CSRF inválido'], 403);
        }
        
        $nombre = isset($put_vars['nombre']) ? sanitizar_input($put_vars['nombre']) : '';
        $descripcion = isset($put_vars['descripcion']) ? sanitizar_input($put_vars['descripcion']) : '';
        $duracion = isset($put_vars['duracion']) ? (int)$put_vars['duracion'] : 0;
        
        $errores = [];
        
        if (empty($nombre)) {
            $errores['nombre'] = 'El nombre es obligatorio';
        }
        
        if (empty($descripcion)) {
            $errores['descripcion'] = 'La descripción es obligatoria';
        }
        
        if ($duracion <= 0) {
            $errores['duracion'] = 'La duración debe ser mayor a 0 minutos';
        }
        
        if (!empty($errores)) {
            json_response(['success' => false, 'message' => 'Hay errores en el formulario', 'errors' => $errores], 400);
        }
        
        $servicios = obtener_servicios();
        
        $servicio_encontrado = false;
        foreach ($servicios as $key => $servicio) {
            if ($servicio['id'] == $id) {
                $servicios[$key]['nombre'] = $nombre;
                $servicios[$key]['descripcion'] = $descripcion;
                $servicios[$key]['duracion'] = $duracion;
                $servicio_encontrado = true;
                break;
            }
        }
        
        if (!$servicio_encontrado) {
            json_response(['success' => false, 'message' => 'Servicio no encontrado'], 404);
        }
        
        if (guardar_servicios($servicios)) {
            json_response(['success' => true, 'message' => 'Servicio actualizado correctamente']);
        } else {
            json_response(['success' => false, 'message' => 'Error al guardar el servicio'], 500);
        }
        break;
        
    case 'DELETE':
        $sesion = verificar_sesion();
        if (!$sesion['logged_in'] || !es_administrador()) {
            json_response(['success' => false, 'message' => 'Acceso denegado'], 403);
        }
        
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID de servicio no válido'], 400);
        }
        
        parse_str(file_get_contents('php://input'), $delete_vars);
        
        if (!isset($delete_vars['csrf_token']) || !verificar_token_csrf($delete_vars['csrf_token'])) {
            json_response(['success' => false, 'message' => 'Token CSRF inválido'], 403);
        }
        
        $servicios = obtener_servicios();
        
        $servicio_encontrado = false;
        foreach ($servicios as $key => $servicio) {
            if ($servicio['id'] == $id) {
                unset($servicios[$key]);
                $servicio_encontrado = true;
                break;
            }
        }
        
        if (!$servicio_encontrado) {
            json_response(['success' => false, 'message' => 'Servicio no encontrado'], 404);
        }
        
        $servicios = array_values($servicios);
        
        if (guardar_servicios($servicios)) {
            json_response(['success' => true, 'message' => 'Servicio eliminado correctamente']);
        } else {
            json_response(['success' => false, 'message' => 'Error al eliminar el servicio'], 500);
        }
        break;
        
    default:
        json_response(['success' => false, 'message' => 'Método no permitido'], 405);
        break;
}
