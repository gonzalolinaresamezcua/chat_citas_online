<?php
/**
 * Archivo de configuración principal
 * Contiene constantes, configuraciones y funciones de inicialización
 */

if (!defined('ACCESO_PERMITIDO')) {
    die('Acceso no autorizado');
}

date_default_timezone_set('America/Mexico_City');

define('BASE_PATH', dirname(__DIR__));
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('DATA_PATH', BASE_PATH . '/data');
define('ASSETS_PATH', BASE_PATH . '/assets');

if (file_exists(BASE_PATH . '/.env')) {
    $env_lines = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

define('OPENAI_API_KEY', getenv('OPENAI_API_KEY'));
define('OPENAI_MODEL', 'gpt-4.1-nano');
define('OPENAI_RATE_LIMIT', 10); // Límite de llamadas por sesión

define('HASH_COST', 10); // Costo para password_hash
define('SESSION_LIFETIME', 3600); // Duración de la sesión en segundos (1 hora)

function iniciar_sesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'secure' => true,     // Solo HTTPS
            'httponly' => true,   // No accesible por JavaScript
            'samesite' => 'Lax'   // Protección CSRF
        ]);
        session_start();
        
        if (!isset($_SESSION['ultima_regeneracion']) || 
            (time() - $_SESSION['ultima_regeneracion']) > 300) { // Cada 5 minutos
            session_regenerate_id(true);
            $_SESSION['ultima_regeneracion'] = time();
        }
    }
}

function generar_token_csrf() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verificar_token_csrf($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

function verificar_archivos_json() {
    $archivos = [
        DATA_PATH . '/usuarios.json',
        DATA_PATH . '/servicios.json',
        DATA_PATH . '/citas.json'
    ];
    
    foreach ($archivos as $archivo) {
        if (!file_exists($archivo)) {
            file_put_contents($archivo, json_encode([]));
            chmod($archivo, 0666); // Permisos de lectura/escritura
        }
    }
}

iniciar_sesion();
verificar_archivos_json();
