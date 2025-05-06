<?php
/**
 * Funciones de autenticación y gestión de usuarios
 */

if (!defined('ACCESO_PERMITIDO')) {
    die('Acceso no autorizado');
}

/**
 * Obtiene todos los usuarios del archivo JSON
 * @return array Lista de usuarios
 */
function obtener_usuarios() {
    $ruta_archivo = DATA_PATH . '/usuarios.json';
    if (file_exists($ruta_archivo)) {
        $json = file_get_contents($ruta_archivo);
        return json_decode($json, true) ?: [];
    }
    return [];
}

/**
 * Guarda la lista de usuarios en el archivo JSON
 * @param array $usuarios Lista de usuarios a guardar
 * @return bool Éxito de la operación
 */
function guardar_usuarios($usuarios) {
    $ruta_archivo = DATA_PATH . '/usuarios.json';
    $json = json_encode($usuarios, JSON_PRETTY_PRINT);
    return file_put_contents($ruta_archivo, $json) !== false;
}

/**
 * Busca un usuario por su ID
 * @param int $id ID del usuario
 * @return array|null Datos del usuario o null si no existe
 */
function obtener_usuario_por_id($id) {
    $usuarios = obtener_usuarios();
    foreach ($usuarios as $usuario) {
        if ($usuario['id'] == $id) {
            return $usuario;
        }
    }
    return null;
}

/**
 * Busca un usuario por su email
 * @param string $email Email del usuario
 * @return array|null Datos del usuario o null si no existe
 */
function obtener_usuario_por_email($email) {
    $usuarios = obtener_usuarios();
    foreach ($usuarios as $usuario) {
        if ($usuario['email'] == $email) {
            return $usuario;
        }
    }
    return null;
}

/**
 * Registra un nuevo usuario
 * @param string $nombre Nombre del usuario
 * @param string $email Email del usuario
 * @param string $password Contraseña sin encriptar
 * @param string $rol Rol del usuario (admin/cliente)
 * @return array Resultado de la operación
 */
function registrar_usuario($nombre, $email, $password, $rol = 'cliente') {
    if (empty($nombre) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Todos los campos son obligatorios'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Email inválido'];
    }
    
    if (obtener_usuario_por_email($email)) {
        return ['success' => false, 'message' => 'El email ya está registrado'];
    }
    
    $usuarios = obtener_usuarios();
    
    $id = 1;
    if (!empty($usuarios)) {
        $ids = array_column($usuarios, 'id');
        $id = max($ids) + 1;
    }
    
    $nuevo_usuario = [
        'id' => $id,
        'nombre' => $nombre,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]),
        'rol' => $rol,
        'fecha_registro' => date('Y-m-d H:i:s')
    ];
    
    $usuarios[] = $nuevo_usuario;
    if (guardar_usuarios($usuarios)) {
        return [
            'success' => true, 
            'message' => 'Usuario registrado correctamente',
            'user_id' => $id
        ];
    } else {
        return ['success' => false, 'message' => 'Error al guardar el usuario'];
    }
}

/**
 * Inicia sesión de usuario
 * @param string $email Email del usuario
 * @param string $password Contraseña sin encriptar
 * @return array Resultado de la operación
 */
function iniciar_sesion_usuario($email, $password) {
    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Email y contraseña son obligatorios'];
    }
    
    $usuario = obtener_usuario_por_email($email);
    if (!$usuario) {
        return ['success' => false, 'message' => 'Usuario no encontrado'];
    }
    
    if (!password_verify($password, $usuario['password'])) {
        return ['success' => false, 'message' => 'Contraseña incorrecta'];
    }
    
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_rol'] = $usuario['rol'];
    
    unset($usuario['password']);
    
    return [
        'success' => true, 
        'message' => 'Sesión iniciada correctamente',
        'user' => $usuario
    ];
}

/**
 * Cierra la sesión actual
 * @return array Resultado de la operación
 */
function cerrar_sesion() {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    
    return ['success' => true, 'message' => 'Sesión cerrada correctamente'];
}

/**
 * Verifica si hay una sesión activa
 * @return array Información de la sesión
 */
function verificar_sesion() {
    if (isset($_SESSION['usuario_id'])) {
        $usuario = obtener_usuario_por_id($_SESSION['usuario_id']);
        if ($usuario) {
            unset($usuario['password']);
            return ['logged_in' => true, 'user' => $usuario];
        }
    }
    return ['logged_in' => false];
}

/**
 * Verifica si el usuario actual tiene rol de administrador
 * @return bool True si es administrador, false en caso contrario
 */
function es_administrador() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
}

/**
 * Verifica si el usuario actual tiene acceso a un recurso
 * @param string $rol_requerido Rol requerido para acceder (admin/cliente)
 * @return bool True si tiene acceso, false en caso contrario
 */
function verificar_acceso($rol_requerido = null) {
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }
    
    if ($rol_requerido === null) {
        return true;
    }
    
    if ($rol_requerido === 'admin') {
        return es_administrador();
    } else if ($rol_requerido === 'cliente') {
        return $_SESSION['usuario_rol'] === 'cliente';
    }
    
    return false;
}

/**
 * Redirige a la página de inicio de sesión si no hay sesión activa
 * @param string $rol_requerido Rol requerido para acceder (admin/cliente)
 */
function requerir_sesion($rol_requerido = null) {
    if (!verificar_acceso($rol_requerido)) {
        $_SESSION['url_redireccion'] = $_SERVER['REQUEST_URI'];
        
        header('Location: ' . BASE_PATH . '/index.php?error=acceso_denegado');
        exit;
    }
}
