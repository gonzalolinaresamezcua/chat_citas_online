<?php
define('ACCESO_PERMITIDO', true);

require_once '../includes/header.php';

requerir_sesion('admin');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$servicios = obtener_servicios();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_servicio'])) {
    if (!isset($_POST['csrf_token']) || !verificar_token_csrf($_POST['csrf_token'])) {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'Token CSRF inválido'
        ];
        redirigir('servicios.php');
    }
    
    $nombre = isset($_POST['nombre']) ? sanitizar_input($_POST['nombre']) : '';
    $descripcion = isset($_POST['descripcion']) ? sanitizar_input($_POST['descripcion']) : '';
    $duracion = isset($_POST['duracion']) ? (int)$_POST['duracion'] : 0;
    $servicio_id = isset($_POST['servicio_id']) ? (int)$_POST['servicio_id'] : 0;
    
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
    
    if (empty($errores)) {
        if ($servicio_id > 0) {
            $servicio_encontrado = false;
            foreach ($servicios as $key => $servicio) {
                if ($servicio['id'] == $servicio_id) {
                    $servicios[$key]['nombre'] = $nombre;
                    $servicios[$key]['descripcion'] = $descripcion;
                    $servicios[$key]['duracion'] = $duracion;
                    $servicio_encontrado = true;
                    break;
                }
            }
            
            if (!$servicio_encontrado) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Servicio no encontrado'
                ];
                redirigir('servicios.php');
            }
            
            if (guardar_servicios($servicios)) {
                $_SESSION['alerta'] = [
                    'tipo' => 'success',
                    'mensaje' => 'Servicio actualizado correctamente'
                ];
                redirigir('servicios.php');
            } else {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Error al guardar el servicio'
                ];
            }
        } else {
            $nuevo_id = generar_id($servicios);
            
            $nuevo_servicio = [
                'id' => $nuevo_id,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'duracion' => $duracion
            ];
            
            $servicios[] = $nuevo_servicio;
            
            if (guardar_servicios($servicios)) {
                $_SESSION['alerta'] = [
                    'tipo' => 'success',
                    'mensaje' => 'Servicio creado correctamente'
                ];
                redirigir('servicios.php');
            } else {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Error al guardar el servicio'
                ];
            }
        }
    }
}

if ($action === 'delete' && $id > 0) {
    if (isset($_GET['csrf_token']) && verificar_token_csrf($_GET['csrf_token'])) {
        $servicio_encontrado = false;
        foreach ($servicios as $key => $servicio) {
            if ($servicio['id'] == $id) {
                unset($servicios[$key]);
                $servicio_encontrado = true;
                break;
            }
        }
        
        if ($servicio_encontrado) {
            $servicios = array_values($servicios);
            
            if (guardar_servicios($servicios)) {
                $_SESSION['alerta'] = [
                    'tipo' => 'success',
                    'mensaje' => 'Servicio eliminado correctamente'
                ];
            } else {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => 'Error al eliminar el servicio'
                ];
            }
        } else {
            $_SESSION['alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Servicio no encontrado'
            ];
        }
        
        redirigir('servicios.php');
    } else {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'Token CSRF inválido'
        ];
        redirigir('servicios.php');
    }
}

$servicio_editar = null;
if ($action === 'edit' && $id > 0) {
    foreach ($servicios as $servicio) {
        if ($servicio['id'] == $id) {
            $servicio_editar = $servicio;
            break;
        }
    }
    
    if (!$servicio_editar) {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'Servicio no encontrado'
        ];
        redirigir('servicios.php');
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-0">Gestión de Servicios</h1>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
            </a>
        </div>
    </div>
</div>

<?php if ($action === 'new' || $action === 'edit'): ?>
<!-- Formulario de creación/edición de servicio -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><?php echo $action === 'new' ? 'Nuevo Servicio' : 'Editar Servicio'; ?></h5>
            </div>
            <div class="card-body">
                <form id="form-servicio" method="POST" action="servicios.php">
                    <input type="hidden" name="csrf_token" value="<?php echo generar_token_csrf(); ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="servicio_id" value="<?php echo $servicio_editar['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Servicio</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $action === 'edit' ? $servicio_editar['nombre'] : ''; ?>" required>
                        <?php if (isset($errores['nombre'])): ?>
                            <div class="text-danger"><?php echo $errores['nombre']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required><?php echo $action === 'edit' ? $servicio_editar['descripcion'] : ''; ?></textarea>
                        <?php if (isset($errores['descripcion'])): ?>
                            <div class="text-danger"><?php echo $errores['descripcion']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="duracion" class="form-label">Duración (minutos)</label>
                        <input type="number" class="form-control" id="duracion" name="duracion" min="1" value="<?php echo $action === 'edit' ? $servicio_editar['duracion'] : '30'; ?>" required>
                        <?php if (isset($errores['duracion'])): ?>
                            <div class="text-danger"><?php echo $errores['duracion']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($action === 'new'): ?>
                    <div class="mb-3">
                        <label for="keywords" class="form-label">Generar descripción con palabras clave (opcional)</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="keywords" placeholder="Ej: peluquería, corte de pelo, estilismo">
                            <button class="btn btn-outline-primary" type="button" id="btn-generar-descripcion">
                                <i class="fas fa-magic me-1"></i> Generar
                            </button>
                        </div>
                        <div class="form-text">Ingresa palabras clave separadas por comas para generar una descripción automáticamente con IA.</div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between">
                        <a href="servicios.php" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" name="guardar_servicio" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> <?php echo $action === 'new' ? 'Crear Servicio' : 'Actualizar Servicio'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Lista de servicios -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Servicios Disponibles</h5>
                <a href="servicios.php?action=new" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Nuevo Servicio
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($servicios)): ?>
                    <p class="text-muted">No hay servicios registrados. ¡Crea el primero!</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Duración</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicios as $servicio): ?>
                                <tr>
                                    <td><?php echo $servicio['id']; ?></td>
                                    <td><?php echo sanitizar_input($servicio['nombre']); ?></td>
                                    <td>
                                        <?php 
                                        $descripcion = sanitizar_input($servicio['descripcion']);
                                        echo strlen($descripcion) > 100 ? substr($descripcion, 0, 100) . '...' : $descripcion;
                                        ?>
                                    </td>
                                    <td><?php echo $servicio['duracion']; ?> min</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="servicios.php?action=edit&id=<?php echo $servicio['id']; ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="servicios.php?action=delete&id=<?php echo $servicio['id']; ?>&csrf_token=<?php echo generar_token_csrf(); ?>" class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este servicio? Esta acción no se puede deshacer.');">
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

<!-- JavaScript para generar descripciones con OpenAI -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnGenerarDescripcion = document.getElementById('btn-generar-descripcion');
    const keywordsInput = document.getElementById('keywords');
    const descripcionTextarea = document.getElementById('descripcion');
    
    if (btnGenerarDescripcion && keywordsInput && descripcionTextarea) {
        btnGenerarDescripcion.addEventListener('click', function() {
            const keywords = keywordsInput.value.trim();
            
            if (keywords === '') {
                alert('Por favor, ingresa palabras clave para generar la descripción.');
                return;
            }
            
            btnGenerarDescripcion.disabled = true;
            btnGenerarDescripcion.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generando...';
            
            const formData = new FormData();
            formData.append('keywords', keywords);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
            
            fetch('../api/servicios.php?action=generate_description', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    descripcionTextarea.value = data.text;
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al conectar con el servidor. Por favor, intenta de nuevo.');
            })
            .finally(() => {
                btnGenerarDescripcion.disabled = false;
                btnGenerarDescripcion.innerHTML = '<i class="fas fa-magic me-1"></i> Generar';
            });
        });
    }
});
</script>

<?php
require_once '../includes/footer.php';
?>
