<?php
/**
 * Encabezado común para todas las páginas
 */

define('ACCESO_PERMITIDO', true);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/openai.php';

$sesion = verificar_sesion();
$usuario_actual = $sesion['logged_in'] ? $sesion['user'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Citas Online</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/styles.css">
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_PATH; ?>/index.php">
                <i class="fas fa-calendar-check me-2"></i>Sistema de Citas
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if ($sesion['logged_in']): ?>
                        <?php if ($usuario_actual['rol'] === 'admin'): ?>
                            <!-- Menú de administrador -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_PATH; ?>/admin/index.php">
                                    <i class="fas fa-tachometer-alt me-1"></i>Panel
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_PATH; ?>/admin/servicios.php">
                                    <i class="fas fa-concierge-bell me-1"></i>Servicios
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_PATH; ?>/admin/citas.php">
                                    <i class="fas fa-calendar-alt me-1"></i>Citas
                                </a>
                            </li>
                        <?php else: ?>
                            <!-- Menú de cliente -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_PATH; ?>/cliente/index.php">
                                    <i class="fas fa-home me-1"></i>Inicio
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_PATH; ?>/cliente/reservar.php">
                                    <i class="fas fa-calendar-plus me-1"></i>Reservar Cita
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_PATH; ?>/cliente/mis-citas.php">
                                    <i class="fas fa-list-alt me-1"></i>Mis Citas
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Menú para visitantes -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_PATH; ?>/index.php">
                                <i class="fas fa-home me-1"></i>Inicio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_PATH; ?>/index.php#servicios">
                                <i class="fas fa-concierge-bell me-1"></i>Servicios
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if ($sesion['logged_in']): ?>
                        <!-- Usuario con sesión -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-1"></i><?php echo $usuario_actual['nombre']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li>
                                    <span class="dropdown-item-text text-muted">
                                        <small><?php echo $usuario_actual['rol'] === 'admin' ? 'Administrador' : 'Cliente'; ?></small>
                                    </span>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo BASE_PATH; ?>/api/auth.php?action=logout">
                                        <i class="fas fa-sign-out-alt me-1"></i>Cerrar Sesión
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Usuario sin sesión -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_PATH; ?>/index.php#login">
                                <i class="fas fa-sign-in-alt me-1"></i>Iniciar Sesión
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_PATH; ?>/index.php#registro">
                                <i class="fas fa-user-plus me-1"></i>Registrarse
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container mb-4">
        <!-- Mensajes de alerta desde la sesión -->
        <?php if (isset($_SESSION['alerta'])): ?>
            <?php echo alerta($_SESSION['alerta']['mensaje'], $_SESSION['alerta']['tipo']); ?>
            <?php unset($_SESSION['alerta']); ?>
        <?php endif; ?>
