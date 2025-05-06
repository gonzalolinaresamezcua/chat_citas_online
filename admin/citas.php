<?php
define('ACCESO_PERMITIDO', true);

require_once '../includes/header.php';

requerir_sesion('admin');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : '';

$citas = obtener_citas();

$servicios = obtener_servicios();
$usuarios = obtener_usuarios();

if ($action === 'confirm' && $id > 0) {
    if (isset($_GET['csrf_token']) && verificar_token_csrf($_GET['csrf_token'])) {
        $cita_encontrada = false;
        foreach ($citas as $key => $cita) {
            if ($cita['id'] == $id) {
                $citas[$key]['estado'] = 'confirmada';
                $cita_encontrada = true;
                break;
            }
        }
        
        if ($cita_encontrada) {
            if (guardar_citas($citas)) {
                $_SESSION['alerta'] = [
                    'tipo' => 'success',
                    'mensaje' => 'Cita confirmada correctamente'
                ];
            } else {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Error al confirmar la cita'
                ];
            }
        } else {
            $_SESSION['alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Cita no encontrada'
            ];
        }
        
        redirigir('citas.php' . ($estado_filtro ? "?estado=$estado_filtro" : ''));
    } else {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'Token CSRF inválido'
        ];
        redirigir('citas.php' . ($estado_filtro ? "?estado=$estado_filtro" : ''));
    }
}

if ($action === 'reject' && $id > 0) {
    if (isset($_GET['csrf_token']) && verificar_token_csrf($_GET['csrf_token'])) {
        $cita_encontrada = false;
        foreach ($citas as $key => $cita) {
            if ($cita['id'] == $id) {
                $citas[$key]['estado'] = 'rechazada';
                $cita_encontrada = true;
                break;
            }
        }
        
        if ($cita_encontrada) {
            if (guardar_citas($citas)) {
                $_SESSION['alerta'] = [
                    'tipo' => 'success',
                    'mensaje' => 'Cita rechazada correctamente'
                ];
            } else {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Error al rechazar la cita'
                ];
            }
        } else {
            $_SESSION['alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Cita no encontrada'
            ];
        }
        
        redirigir('citas.php' . ($estado_filtro ? "?estado=$estado_filtro" : ''));
    } else {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'Token CSRF inválido'
        ];
        redirigir('citas.php' . ($estado_filtro ? "?estado=$estado_filtro" : ''));
    }
}

if ($action === 'delete' && $id > 0) {
    if (isset($_GET['csrf_token']) && verificar_token_csrf($_GET['csrf_token'])) {
        $cita_encontrada = false;
        foreach ($citas as $key => $cita) {
            if ($cita['id'] == $id) {
                unset($citas[$key]);
                $cita_encontrada = true;
                break;
            }
        }
        
        if ($cita_encontrada) {
            $citas = array_values($citas);
            
            if (guardar_citas($citas)) {
                $_SESSION['alerta'] = [
                    'tipo' => 'success',
                    'mensaje' => 'Cita eliminada correctamente'
                ];
            } else {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Error al eliminar la cita'
                ];
            }
        } else {
            $_SESSION['alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Cita no encontrada'
            ];
        }
        
        redirigir('citas.php' . ($estado_filtro ? "?estado=$estado_filtro" : ''));
    } else {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'Token CSRF inválido'
        ];
        redirigir('citas.php' . ($estado_filtro ? "?estado=$estado_filtro" : ''));
    }
}

$cita_detalle = null;
if ($action === 'view' && $id > 0) {
    foreach ($citas as $cita) {
        if ($cita['id'] == $id) {
            $cita_detalle = $cita;
            break;
        }
    }
    
    if (!$cita_detalle) {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'Cita no encontrada'
        ];
        redirigir('citas.php' . ($estado_filtro ? "?estado=$estado_filtro" : ''));
    }
}

if ($estado_filtro) {
    $citas_filtradas = [];
    foreach ($citas as $cita) {
        if ($cita['estado'] === $estado_filtro) {
            $citas_filtradas[] = $cita;
        }
    }
    $citas = $citas_filtradas;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_cita'])) {
    if (!isset($_POST['csrf_token']) || !verificar_token_csrf($_POST['csrf_token'])) {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'Token CSRF inválido'
        ];
        redirigir('citas.php');
    }
    
    $usuario_id = isset($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : 0;
    $servicio_id = isset($_POST['servicio_id']) ? (int)$_POST['servicio_id'] : 0;
    $fecha = isset($_POST['fecha']) ? sanitizar_input($_POST['fecha']) : '';
    $hora = isset($_POST['hora']) ? sanitizar_input($_POST['hora']) : '';
    $estado = isset($_POST['estado']) ? sanitizar_input($_POST['estado']) : 'pendiente';
    
    $errores = [];
    
    if ($usuario_id <= 0) {
        $errores['usuario_id'] = 'Debes seleccionar un cliente';
    }
    
    if ($servicio_id <= 0) {
        $errores['servicio_id'] = 'Debes seleccionar un servicio';
    }
    
    if (empty($fecha)) {
        $errores['fecha'] = 'La fecha es obligatoria';
    }
    
    if (empty($hora)) {
        $errores['hora'] = 'La hora es obligatoria';
    }
    
    if (empty($errores)) {
        $fecha_hora = $fecha . ' ' . $hora;
        
        if (!verificar_disponibilidad($servicio_id, $fecha_hora)) {
            $errores['hora'] = 'La hora seleccionada no está disponible';
        } else {
            $nuevo_id = generar_id($citas);
            
            $servicio = null;
            foreach ($servicios as $s) {
                if ($s['id'] == $servicio_id) {
                    $servicio = $s;
                    break;
                }
            }
            
            $mensaje = "Tu cita para " . ($servicio ? $servicio['nombre'] : 'el servicio seleccionado') . 
                       " está programada el " . date('d/m/Y', strtotime($fecha)) . 
                       " a las " . date('H:i', strtotime($hora)) . ". ¡Gracias!";
            
            $nueva_cita = [
                'id' => $nuevo_id,
                'usuario_id' => $usuario_id,
                'servicio_id' => $servicio_id,
                'fecha' => $fecha_hora,
                'estado' => $estado,
                'mensaje' => $mensaje
            ];
            
            $citas[] = $nueva_cita;
            
            if (guardar_citas($citas)) {
                $_SESSION['alerta'] = [
                    'tipo' => 'success',
                    'mensaje' => 'Cita creada correctamente'
                ];
                redirigir('citas.php');
            } else {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Error al guardar la cita'
                ];
            }
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-0">Gestión de Citas</h1>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
            </a>
        </div>
    </div>
</div>

<?php if ($action === 'new'): ?>
<!-- Formulario de creación de cita -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Nueva Cita</h5>
            </div>
            <div class="card-body">
                <form id="form-cita" method="POST" action="citas.php">
                    <input type="hidden" name="csrf_token" value="<?php echo generar_token_csrf(); ?>">
                    
                    <div class="mb-3">
                        <label for="usuario_id" class="form-label">Cliente</label>
                        <select class="form-select" id="usuario_id" name="usuario_id" required>
                            <option value="">Selecciona un cliente</option>
                            <?php 
                            foreach ($usuarios as $usuario) {
                                if ($usuario['rol'] === 'cliente') {
                                    echo '<option value="' . $usuario['id'] . '">' . sanitizar_input($usuario['nombre']) . ' (' . sanitizar_input($usuario['email']) . ')</option>';
                                }
                            }
                            ?>
                        </select>
                        <?php if (isset($errores['usuario_id'])): ?>
                            <div class="text-danger"><?php echo $errores['usuario_id']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="servicio_id" class="form-label">Servicio</label>
                        <select class="form-select" id="servicio_id" name="servicio_id" required>
                            <option value="">Selecciona un servicio</option>
                            <?php 
                            foreach ($servicios as $servicio) {
                                echo '<option value="' . $servicio['id'] . '">' . sanitizar_input($servicio['nombre']) . ' (' . $servicio['duracion'] . ' min)</option>';
                            }
                            ?>
                        </select>
                        <?php if (isset($errores['servicio_id'])): ?>
                            <div class="text-danger"><?php echo $errores['servicio_id']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" min="<?php echo date('Y-m-d'); ?>" required>
                            <?php if (isset($errores['fecha'])): ?>
                                <div class="text-danger"><?php echo $errores['fecha']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="hora" class="form-label">Hora</label>
                            <input type="time" class="form-control" id="hora" name="hora" required>
                            <?php if (isset($errores['hora'])): ?>
                                <div class="text-danger"><?php echo $errores['hora']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="pendiente">Pendiente</option>
                            <option value="confirmada">Confirmada</option>
                            <option value="rechazada">Rechazada</option>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="citas.php" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" name="guardar_cita" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Crear Cita
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php elseif ($action === 'view' && $cita_detalle): ?>
<!-- Detalles de la cita -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detalles de la Cita #<?php echo $cita_detalle['id']; ?></h5>
                <a href="citas.php<?php echo $estado_filtro ? "?estado=$estado_filtro" : ''; ?>" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
            <div class="card-body">
                <?php
                $cliente = obtener_usuario_por_id($cita_detalle['usuario_id']);
                $nombre_cliente = $cliente ? $cliente['nombre'] : 'Usuario desconocido';
                $email_cliente = $cliente ? $cliente['email'] : 'N/A';
                
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
                        <h6 class="fw-bold">Información del Cliente</h6>
                        <p><strong>Nombre:</strong> <?php echo sanitizar_input($nombre_cliente); ?></p>
                        <p><strong>Email:</strong> <?php echo sanitizar_input($email_cliente); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Información del Servicio</h6>
                        <p><strong>Nombre:</strong> <?php echo $servicio ? sanitizar_input($servicio['nombre']) : 'Servicio desconocido'; ?></p>
                        <p><strong>Descripción:</strong> <?php echo $servicio ? sanitizar_input($servicio['descripcion']) : 'N/A'; ?></p>
                        <p><strong>Duración:</strong> <?php echo $servicio ? $servicio['duracion'] . ' minutos' : 'N/A'; ?></p>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Detalles de la Cita</h6>
                        <p><strong>Fecha y hora:</strong> <?php echo formatear_fecha($cita_detalle['fecha']); ?></p>
                        <p>
                            <strong>Estado:</strong> 
                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($cita_detalle['estado']); ?></span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Mensaje de Confirmación</h6>
                        <div class="alert alert-light">
                            <?php echo isset($cita_detalle['mensaje']) ? sanitizar_input($cita_detalle['mensaje']) : 'No hay mensaje de confirmación'; ?>
                        </div>
                    </div>
                </div>
                
                <?php if ($cita_detalle['estado'] === 'pendiente'): ?>
                <hr>
                
                <div class="d-flex justify-content-end">
                    <a href="citas.php?action=confirm&id=<?php echo $cita_detalle['id']; ?>&csrf_token=<?php echo generar_token_csrf(); ?><?php echo $estado_filtro ? "&estado=$estado_filtro" : ''; ?>" class="btn btn-success me-2">
                        <i class="fas fa-check me-1"></i> Confirmar Cita
                    </a>
                    <a href="citas.php?action=reject&id=<?php echo $cita_detalle['id']; ?>&csrf_token=<?php echo generar_token_csrf(); ?><?php echo $estado_filtro ? "&estado=$estado_filtro" : ''; ?>" class="btn btn-danger me-2">
                        <i class="fas fa-times me-1"></i> Rechazar Cita
                    </a>
                    <a href="citas.php?action=delete&id=<?php echo $cita_detalle['id']; ?>&csrf_token=<?php echo generar_token_csrf(); ?><?php echo $estado_filtro ? "&estado=$estado_filtro" : ''; ?>" class="btn btn-outline-danger" onclick="return confirm('¿Estás seguro de eliminar esta cita? Esta acción no se puede deshacer.');">
                        <i class="fas fa-trash me-1"></i> Eliminar Cita
                    </a>
                </div>
                <?php else: ?>
                <hr>
                
                <div class="d-flex justify-content-end">
                    <a href="citas.php?action=delete&id=<?php echo $cita_detalle['id']; ?>&csrf_token=<?php echo generar_token_csrf(); ?><?php echo $estado_filtro ? "&estado=$estado_filtro" : ''; ?>" class="btn btn-outline-danger" onclick="return confirm('¿Estás seguro de eliminar esta cita? Esta acción no se puede deshacer.');">
                        <i class="fas fa-trash me-1"></i> Eliminar Cita
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
                            Todas las Citas
                        <?php endif; ?>
                    </h5>
                    <div class="d-flex">
                        <div class="btn-group me-2">
                            <a href="citas.php" class="btn btn-outline-primary <?php echo !$estado_filtro ? 'active' : ''; ?>">
                                Todas
                            </a>
                            <a href="citas.php?estado=pendiente" class="btn btn-outline-warning <?php echo $estado_filtro === 'pendiente' ? 'active' : ''; ?>">
                                Pendientes
                            </a>
                            <a href="citas.php?estado=confirmada" class="btn btn-outline-success <?php echo $estado_filtro === 'confirmada' ? 'active' : ''; ?>">
                                Confirmadas
                            </a>
                            <a href="citas.php?estado=rechazada" class="btn btn-outline-danger <?php echo $estado_filtro === 'rechazada' ? 'active' : ''; ?>">
                                Rechazadas
                            </a>
                        </div>
                        <a href="citas.php?action=new" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Nueva Cita
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($citas)): ?>
                    <p class="text-muted">
                        <?php if ($estado_filtro): ?>
                            No hay citas <?php echo $estado_filtro; ?>s.
                        <?php else: ?>
                            No hay citas registradas en el sistema.
                        <?php endif; ?>
                    </p>
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
                                
                                foreach ($citas as $cita): 
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
                                            <a href="citas.php?action=view&id=<?php echo $cita['id']; ?><?php echo $estado_filtro ? "&estado=$estado_filtro" : ''; ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($cita['estado'] === 'pendiente'): ?>
                                                <a href="citas.php?action=confirm&id=<?php echo $cita['id']; ?>&csrf_token=<?php echo generar_token_csrf(); ?><?php echo $estado_filtro ? "&estado=$estado_filtro" : ''; ?>" class="btn btn-outline-success" data-bs-toggle="tooltip" title="Confirmar">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="citas.php?action=reject&id=<?php echo $cita['id']; ?>&csrf_token=<?php echo generar_token_csrf(); ?><?php echo $estado_filtro ? "&estado=$estado_filtro" : ''; ?>" class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Rechazar">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="citas.php?action=delete&id=<?php echo $cita['id']; ?>&csrf_token=<?php echo generar_token_csrf(); ?><?php echo $estado_filtro ? "&estado=$estado_filtro" : ''; ?>" class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta cita? Esta acción no se puede deshacer.');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
require_once '../includes/footer.php';
?>
