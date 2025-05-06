<?php
define('ACCESO_PERMITIDO', true);

require_once '../includes/header.php';

requerir_sesion('admin');

$usuarios = obtener_usuarios();
$total_usuarios = count($usuarios);
$total_clientes = 0;
foreach ($usuarios as $usuario) {
    if ($usuario['rol'] === 'cliente') {
        $total_clientes++;
    }
}

$servicios = obtener_servicios();
$total_servicios = count($servicios);

$citas = obtener_citas();
$total_citas = count($citas);
$citas_pendientes = 0;
$citas_confirmadas = 0;
$citas_rechazadas = 0;

foreach ($citas as $cita) {
    if ($cita['estado'] === 'pendiente') {
        $citas_pendientes++;
    } elseif ($cita['estado'] === 'confirmada') {
        $citas_confirmadas++;
    } elseif ($cita['estado'] === 'rechazada') {
        $citas_rechazadas++;
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-4">Panel de Administración</h1>
        <p class="lead">Bienvenido, <?php echo $usuario_actual['nombre']; ?>. Desde aquí puedes gestionar todos los aspectos del sistema de citas.</p>
    </div>
</div>

<!-- Tarjetas de estadísticas -->
<div class="row mb-4">
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title">Usuarios</h5>
                <p class="card-text display-4"><?php echo $total_clientes; ?></p>
                <p class="card-text">Clientes registrados en el sistema</p>
            </div>
            <div class="card-footer bg-transparent border-top-0">
                <a href="#" class="text-white">Ver detalles <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h5 class="card-title">Servicios</h5>
                <p class="card-text display-4"><?php echo $total_servicios; ?></p>
                <p class="card-text">Servicios disponibles para reserva</p>
            </div>
            <div class="card-footer bg-transparent border-top-0">
                <a href="servicios.php" class="text-white">Gestionar <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info h-100">
            <div class="card-body">
                <h5 class="card-title">Citas Totales</h5>
                <p class="card-text display-4"><?php echo $total_citas; ?></p>
                <p class="card-text">Citas registradas en el sistema</p>
            </div>
            <div class="card-footer bg-transparent border-top-0">
                <a href="citas.php" class="text-white">Ver todas <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-warning h-100">
            <div class="card-body">
                <h5 class="card-title">Citas Pendientes</h5>
                <p class="card-text display-4"><?php echo $citas_pendientes; ?></p>
                <p class="card-text">Citas que requieren tu atención</p>
            </div>
            <div class="card-footer bg-transparent border-top-0">
                <a href="citas.php?estado=pendiente" class="text-white">Revisar <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Citas recientes -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Citas Recientes</h5>
            </div>
            <div class="card-body">
                <?php if (empty($citas)): ?>
                    <p class="text-muted">No hay citas registradas en el sistema.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Servicio</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                usort($citas, function($a, $b) {
                                    return strtotime($b['fecha']) - strtotime($a['fecha']);
                                });
                                
                                $citas_recientes = array_slice($citas, 0, 5);
                                
                                foreach ($citas_recientes as $cita): 
                                    $cliente = obtener_usuario_por_id($cita['usuario_id']);
                                    $nombre_cliente = $cliente ? $cliente['nombre'] : 'Usuario desconocido';
                                    
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
                                    <td><?php echo $cita['id']; ?></td>
                                    <td><?php echo sanitizar_input($nombre_cliente); ?></td>
                                    <td><?php echo sanitizar_input($nombre_servicio); ?></td>
                                    <td><?php echo formatear_fecha($cita['fecha']); ?></td>
                                    <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($cita['estado']); ?></span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="citas.php?action=view&id=<?php echo $cita['id']; ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($cita['estado'] === 'pendiente'): ?>
                                                <a href="citas.php?action=confirm&id=<?php echo $cita['id']; ?>&csrf_token=<?php echo generar_token_csrf(); ?>" class="btn btn-outline-success" data-bs-toggle="tooltip" title="Confirmar">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="citas.php?action=reject&id=<?php echo $cita['id']; ?>&csrf_token=<?php echo generar_token_csrf(); ?>" class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Rechazar">
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
                        <a href="citas.php" class="btn btn-primary">Ver todas las citas</a>
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
                    <div class="col-md-4 mb-3">
                        <a href="servicios.php?action=new" class="btn btn-outline-primary d-block p-3">
                            <i class="fas fa-plus-circle fa-2x mb-2"></i>
                            <div>Agregar Nuevo Servicio</div>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="citas.php?estado=pendiente" class="btn btn-outline-warning d-block p-3">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <div>Gestionar Citas Pendientes</div>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="citas.php?action=new" class="btn btn-outline-success d-block p-3">
                            <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                            <div>Crear Nueva Cita</div>
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
