<?php
/**
 * API para autenticación de usuarios
 * Maneja registro, inicio de sesión, cierre de sesión y verificación de sesión
 */

define('ACCESO_PERMITIDO', true);

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($method) {
    case 'GET':
        if ($action === 'logout') {
            $resultado = cerrar_sesion();
            json_response($resultado);
        } elseif ($action === 'check') {
            $resultado = verificar_sesion();
            json_response($resultado);
        } else {
            json_response(['success' => false, 'message' => 'Acción no válida'], 400);
        }
        break;
        
    case 'POST':
        if ($action === 'register') {
            
            if (!isset($_POST['csrf_token']) || !verificar_token_csrf($_POST['csrf_token'])) {
                json_response(['success' => false, 'message' => 'Token CSRF inválido'], 403);
            }
            
            $nombre = isset($_POST['nombre']) ? sanitizar_input($_POST['nombre']) : '';
            $email = isset($_POST['email']) ? sanitizar_input($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
            
            $errores = [];
            
            if (empty($nombre)) {
                $errores['nombre'] = 'El nombre es obligatorio';
            }
            
            if (empty($email)) {
                $errores['email'] = 'El email es obligatorio';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores['email'] = 'El email no es válido';
            }
            
            if (empty($password)) {
                $errores['password'] = 'La contraseña es obligatoria';
            } elseif (strlen($password) < 8) {
                $errores['password'] = 'La contraseña debe tener al menos 8 caracteres';
            }
            
            if ($password !== $password_confirm) {
                $errores['password_confirm'] = 'Las contraseñas no coinciden';
            }
            
            if (!empty($errores)) {
                json_response(['success' => false, 'message' => 'Hay errores en el formulario', 'errors' => $errores], 400);
            }
            
            $resultado = registrar_usuario($nombre, $email, $password);
            
            json_response($resultado, $resultado['success'] ? 201 : 400);
        } elseif ($action === 'login') {
            
            if (!isset($_POST['csrf_token']) || !verificar_token_csrf($_POST['csrf_token'])) {
                json_response(['success' => false, 'message' => 'Token CSRF inválido'], 403);
            }
            
            $email = isset($_POST['email']) ? sanitizar_input($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            $errores = [];
            
            if (empty($email)) {
                $errores['email'] = 'El email es obligatorio';
            }
            
            if (empty($password)) {
                $errores['password'] = 'La contraseña es obligatoria';
            }
            
            if (!empty($errores)) {
                json_response(['success' => false, 'message' => 'Hay errores en el formulario', 'errors' => $errores], 400);
            }
            
            $resultado = iniciar_sesion_usuario($email, $password);
            
            json_response($resultado, $resultado['success'] ? 200 : 401);
        } else {
            json_response(['success' => false, 'message' => 'Acción no válida'], 400);
        }
        break;
        
    default:
        json_response(['success' => false, 'message' => 'Método no permitido'], 405);
        break;
}
