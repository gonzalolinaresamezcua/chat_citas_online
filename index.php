<?php
define('ACCESO_PERMITIDO', true);

require_once 'includes/header.php';

$servicios = obtener_servicios();
?>

<!-- Sección Hero -->
<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-4">Sistema de Control de Citas Online</h1>
        <p class="lead">Gestiona tus citas de forma fácil y eficiente</p>
        <?php if (!$sesion['logged_in']): ?>
            <div class="mt-4">
                <a href="#registro" class="btn btn-primary btn-lg me-2">Registrarse</a>
                <a href="#login" class="btn btn-outline-primary btn-lg">Iniciar Sesión</a>
            </div>
        <?php else: ?>
            <div class="mt-4">
                <?php if ($usuario_actual['rol'] === 'admin'): ?>
                    <a href="<?php echo BASE_PATH; ?>/admin/index.php" class="btn btn-primary btn-lg">Ir al Panel de Administración</a>
                <?php else: ?>
                    <a href="<?php echo BASE_PATH; ?>/cliente/reservar.php" class="btn btn-primary btn-lg">Reservar una Cita</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Sección de Servicios -->
<section id="servicios" class="services-section py-5">
    <div class="container">
        <h2 class="text-center mb-4">Nuestros Servicios</h2>
        
        <?php if (empty($servicios)): ?>
            <div class="alert alert-info">
                <p class="mb-0">Aún no hay servicios disponibles. Vuelve pronto para ver nuestras ofertas.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($servicios as $servicio): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo sanitizar_input($servicio['nombre']); ?></h5>
                                <p class="card-text"><?php echo sanitizar_input($servicio['descripcion']); ?></p>
                                <p class="card-text"><small class="text-muted">Duración: <?php echo $servicio['duracion']; ?> minutos</small></p>
                            </div>
                            <div class="card-footer bg-transparent border-top-0">
                                <?php if ($sesion['logged_in'] && $usuario_actual['rol'] === 'cliente'): ?>
                                    <a href="<?php echo BASE_PATH; ?>/cliente/reservar.php?servicio_id=<?php echo $servicio['id']; ?>" class="btn btn-primary">Reservar</a>
                                <?php elseif (!$sesion['logged_in']): ?>
                                    <a href="#login" class="btn btn-outline-primary">Iniciar sesión para reservar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (!$sesion['logged_in']): ?>
<!-- Sección de Autenticación -->
<section id="auth" class="auth-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <!-- Formulario de Inicio de Sesión -->
            <div class="col-md-6 mb-4" id="login">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Iniciar Sesión</h3>
                    </div>
                    <div class="card-body">
                        <form id="login-form" method="post" action="api/auth.php?action=login">
                            <input type="hidden" name="csrf_token" value="<?php echo generar_token_csrf(); ?>">
                            
                            <div class="mb-3">
                                <label for="login-email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="login-email" name="email" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="login-password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="login-password" name="password" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                            </div>
                            
                            <div class="alert alert-danger mt-3 d-none" id="login-error"></div>
                            <div class="alert alert-success mt-3 d-none" id="login-success"></div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Formulario de Registro -->
            <div class="col-md-6 mb-4" id="registro">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0">Registrarse</h3>
                    </div>
                    <div class="card-body">
                        <form id="registro-form" method="post" action="api/auth.php?action=register">
                            <input type="hidden" name="csrf_token" value="<?php echo generar_token_csrf(); ?>">
                            
                            <div class="mb-3">
                                <label for="registro-nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="registro-nombre" name="nombre" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="registro-email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="registro-email" name="email" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="registro-password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="registro-password" name="password" required>
                                <div class="form-text">La contraseña debe tener al menos 8 caracteres.</div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="registro-password-confirm" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="registro-password-confirm" name="password_confirm" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Registrarse</button>
                            </div>
                            
                            <div class="alert alert-danger mt-3 d-none" id="registro-error"></div>
                            <div class="alert alert-success mt-3 d-none" id="registro-success"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Script para validación de formularios -->
<script src="<?php echo BASE_PATH; ?>/assets/js/auth.js"></script>

<?php
require_once 'includes/footer.php';
?>
