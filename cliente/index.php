<?php
define('ACCESO_PERMITIDO', true);

require_once '../includes/header.php';

requerir_sesion('cliente');

$usuario_id = $usuario_actual['id'];

$citas = obtener_citas();
$citas_usuario = [];
foreach ($citas as $cita) {
    if ($cita['usuario_id'] == $usuario_id) {
        $citas_usuario[] = $cita;
    }
}

$servicios = obtener_servicios();
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-4">Panel de Cliente</h1>
        <p class="lead">Bienvenido, <?php echo $usuario_actual['nombre']; ?>. Desde aquí puedes gestionar tus citas y reservar nuevos servicios.</p>
    </div>
</div>

<!-- Tarjetas de estadísticas -->
<div class="row mb-4">
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title">Mis Citas</h5>
                <p class="card-text display-4"><?php echo count($citas_usuario); ?></p>
                <p class="card-text">Total de citas registradas</p>
            </div>
            <div class="card-footer bg-transparent border-top-0">
                <a href="mis-citas.php" class="text-white">Ver todas <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h5 class="card-title">Servicios Disponibles</h5>
                <p class="card-text display-4"><?php echo count($servicios); ?></p>
                <p class="card-text">Servicios que puedes reservar</p>
            </div>
            <div class="card-footer bg-transparent border-top-0">
                <a href="reservar.php" class="text-white">Reservar <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    
    <?php
    $citas_pendientes = 0;
    foreach ($citas_usuario as $cita) {
        if ($cita['estado'] === 'pendiente') {
            $citas_pendientes++;
        }
    }
    ?>
    
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-warning h-100">
            <div class="card-body">
                <h5 class="card-title">Citas Pendientes</h5>
                <p class="card-text display-4"><?php echo $citas_pendientes; ?></p>
                <p class="card-text">Citas en espera de confirmación</p>
            </div>
            <div class="card-footer bg-transparent border-top-0">
                <a href="mis-citas.php?estado=pendiente" class="text-white">Ver pendientes <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Citas recientes -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Mis Citas Recientes</h5>
            </div>
            <div class="card-body">
                <?php if (empty($citas_usuario)): ?>
                    <p class="text-muted">No tienes citas registradas. ¡Reserva tu primera cita ahora!</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                usort($citas_usuario, function($a, $b) {
                                    return strtotime($b['fecha']) - strtotime($a['fecha']);
                                });
                                
                                $citas_recientes = array_slice($citas_usuario, 0, 5);
                                
                                foreach ($citas_recientes as $cita): 
                                    $servicio = null;
                                    foreach ($servicios as $s) {
                                        if ($s['id'] == $cita['servicio_id']) {
                                            $servicio = $s;
                                            break;
                                        }
                                    }
                                    $nombre_servicio = $servicio ? $servicio['nombre'] : 'Servicio desconocido';
                                    
                                    $badge_class = '';
                                    switch ($cita['estado']) {
                                        case 'confirmada':
                                            $badge_class = 'bg-success';
                                            break;
                                        case 'pendiente':
                                            $badge_class = 'bg-warning text-dark';
                                            break;
                                        case 'rechazada':
                                            $badge_class = 'bg-danger';
                                            break;
                                    }
                                ?>
                                <tr>
                                    <td><?php echo sanitizar_input($nombre_servicio); ?></td>
                                    <td><?php echo formatear_fecha($cita['fecha']); ?></td>
                                    <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($cita['estado']); ?></span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="mis-citas.php?action=view&id=<?php echo $cita['id']; ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($cita['estado'] === 'pendiente'): ?>
                                                <a href="mis-citas.php?action=cancel&id=<?php echo $cita['id']; ?>&csrf_token=<?php echo generar_token_csrf(); ?>" class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Cancelar" onclick="return confirm('¿Estás seguro de cancelar esta cita?');">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="mis-citas.php" class="btn btn-primary">Ver todas mis citas</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Acciones rápidas -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="reservar.php" class="btn btn-outline-primary d-block p-3">
                            <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                            <div>Reservar Nueva Cita</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="mis-citas.php" class="btn btn-outline-info d-block p-3">
                            <i class="fas fa-list-alt fa-2x mb-2"></i>
                            <div>Ver Historial de Citas</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
