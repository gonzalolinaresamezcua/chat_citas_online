<?php
define('ACCESO_PERMITIDO', true);

require_once '../includes/header.php';

requerir_sesion('cliente');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : '';

$mostrar_exito = isset($_GET['success']) && $_GET['success'] == 1;

$usuario_id = $usuario_actual['id'];

$citas = obtener_citas();
$citas_usuario = [];
foreach ($citas as $cita) {
    if ($cita['usuario_id'] == $usuario_id) {
        $citas_usuario[] = $cita;
    }
}

if ($estado_filtro) {
    $citas_filtradas = [];
    foreach ($citas_usuario as $cita) {
        if ($cita['estado'] === $estado_filtro) {
            $citas_filtradas[] = $cita;
        }
    }
    $citas_usuario = $citas_filtradas;
}

$servicios = obtener_servicios();

if ($action === 'cancel' && $id > 0) {
    if (isset($_GET['csrf_token']) && verificar_token_csrf($_GET['csrf_token'])) {
        $cita_encontrada = false;
        foreach ($citas as $key => $cita) {
            if ($cita['id'] == $id && $cita['usuario_id'] == $usuario_id) {
                if ($cita['estado'] === 'pendiente') {
                    unset($citas[$key]);
                    $cita_encontrada = true;
                } else {
                    $_SESSION['alerta'] = [
                        'tipo' => 'danger',
                        'mensaje' => 'Solo puedes cancelar citas en estado pendiente'
                    ];
                }
                break;
            }
        }
        
        if ($cita_encontrada) {
            $citas = array_values($citas);
            
            guardar_citas($citas);
            
            $_SESSION['alerta'] = [
                'tipo' => 'success',
                'mensaje' => 'Cita cancelada correctamente'
            ];
        } else {
            $_SESSION['alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Cita no encontrada o no tienes permisos para cancelarla'
            ];
        }
        
        redirigir('mis-citas.php' . ($estado_filtro ? "?estado=$estado_filtro" : ''));
    } else {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'Token CSRF inválido'
        ];
        
        redirigir('mis-citas.php' . ($estado_filtro ? "?estado=$estado_filtro" : ''));
    }
}

$cita_detalle = null;
if ($action === 'view' && $id > 0) {
    foreach ($citas as $cita) {
        if ($cita['id'] == $id && $cita['usuario_id'] == $usuario_id) {
            $cita_detalle = $cita;
            break;
        }
    }
    
    if (!$cita_detalle) {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'Cita no encontrada o no tienes permisos para verla'
        ];
        
        redirigir('mis-citas.php' . ($estado_filtro ? "?estado=$estado_filtro" : ''));
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-4">Mis Citas</h1>
        <p class="lead">Gestiona tus citas y consulta su estado.</p>
    </div>
</div>

<?php if ($mostrar_exito): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <strong>¡Éxito!</strong> Tu cita ha sido reservada correctamente. Ahora está en estado pendiente hasta que sea confirmada por un administrador.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if ($action === 'view' && $cita_detalle): ?>
<!-- Detalles de la cita -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detalles de la Cita #<?php echo $cita_detalle['id']; ?></h5>
                <a href="mis-citas.php<?php echo $estado_filtro ? "?estado=$estado_filtro" : ''; ?>" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
            <div class="card-body">
                <?php
                $servicio = null;
                foreach ($servicios as $s) {
                    if ($s['id'] == $cita_detalle['servicio_id']) {
                        $servicio = $s;
                        break;
                    }
                }
                
                $badge_class = '';
                switch ($cita_detalle['estado']) {
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
                
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Información del Servicio</h6>
                        <p><strong>Nombre:</strong> <?php echo $servicio ? sanitizar_input($servicio['nombre']) : 'Servicio desconocido'; ?></p>
                        <p><strong>Descripción:</strong> <?php echo $servicio ? sanitizar_input($servicio['descripcion']) : 'N/A'; ?></p>
                        <p><strong>Duración:</strong> <?php echo $servicio ? $servicio['duracion'] . ' minutos' : 'N/A'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Detalles de la Cita</h6>
                        <p><strong>Fecha y hora:</strong> <?php echo formatear_fecha($cita_detalle['fecha']); ?></p>
                        <p>
                            <strong>Estado:</strong> 
                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($cita_detalle['estado']); ?></span>
                        </p>
                        <?php if ($cita_detalle['estado'] === 'pendiente'): ?>
                            <p class="text-muted"><small>Tu cita está pendiente de confirmación por un administrador.</small></p>
                        <?php elseif ($cita_detalle['estado'] === 'confirmada'): ?>
                            <p class="text-success"><small>¡Tu cita ha sido confirmada! Te esperamos en la fecha y hora indicada.</small></p>
                        <?php elseif ($cita_detalle['estado'] === 'rechazada'): ?>
                            <p class="text-danger"><small>Lo sentimos, tu cita ha sido rechazada. Por favor, intenta reservar en otra fecha u horario.</small></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="fw-bold">Mensaje de Confirmación</h6>
                        <div class="alert alert-light">
                            <?php echo isset($cita_detalle['mensaje']) ? sanitizar_input($cita_detalle['mensaje']) : 'No hay mensaje de confirmación'; ?>
                        </div>
                    </div>
                </div>
                
                <?php if ($cita_detalle['estado'] === 'pendiente'): ?>
                <hr>
                
                <div class="d-flex justify-content-end">
                    <a href="mis-citas.php?action=cancel&id=<?php echo $cita_detalle['id']; ?>&csrf_token=<?php echo generar_token_csrf(); ?><?php echo $estado_filtro ? "&estado=$estado_filtro" : ''; ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de cancelar esta cita?');">
                        <i class="fas fa-times me-1"></i> Cancelar Cita
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Lista de citas -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <?php if ($estado_filtro): ?>
                            Citas <?php echo ucfirst($estado_filtro); ?>s
                        <?php else: ?>
                            Todas mis Citas
                        <?php endif; ?>
                    </h5>
                    <div>
                        <a href="mis-citas.php" class="btn btn-outline-primary me-2 <?php echo !$estado_filtro ? 'active' : ''; ?>">
                            Todas
                        </a>
                        <a href="mis-citas.php?estado=pendiente" class="btn btn-outline-warning me-2 <?php echo $estado_filtro === 'pendiente' ? 'active' : ''; ?>">
                            Pendientes
                        </a>
                        <a href="mis-citas.php?estado=confirmada" class="btn btn-outline-success me-2 <?php echo $estado_filtro === 'confirmada' ? 'active' : ''; ?>">
                            Confirmadas
                        </a>
                        <a href="mis-citas.php?estado=rechazada" class="btn btn-outline-danger <?php echo $estado_filtro === 'rechazada' ? 'active' : ''; ?>">
                            Rechazadas
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($citas_usuario)): ?>
                    <p class="text-muted">
                        <?php if ($estado_filtro): ?>
                            No tienes citas <?php echo $estado_filtro; ?>s.
                        <?php else: ?>
                            No tienes citas registradas. <a href="reservar.php">¡Reserva tu primera cita ahora!</a>
                        <?php endif; ?>
                    </p>
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
                                
                                foreach ($citas_usuario as $cita): 
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
                                            <a href="mis-citas.php?action=view&id=<?php echo $cita['id']; ?><?php echo $estado_filtro ? "&estado=$estado_filtro" : ''; ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($cita['estado'] === 'pendiente'): ?>
                                                <a href="mis-citas.php?action=cancel&id=<?php echo $cita['id']; ?>&csrf_token=<?php echo generar_token_csrf(); ?><?php echo $estado_filtro ? "&estado=$estado_filtro" : ''; ?>" class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Cancelar" onclick="return confirm('¿Estás seguro de cancelar esta cita?');">
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
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white">
                <a href="reservar.php" class="btn btn-primary">
                    <i class="fas fa-calendar-plus me-1"></i> Reservar Nueva Cita
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
require_once '../includes/footer.php';
?>
