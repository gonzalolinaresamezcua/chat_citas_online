<?php
define('ACCESO_PERMITIDO', true);

require_once '../includes/header.php';

requerir_sesion('cliente');

$servicios = obtener_servicios();

$servicio_id = isset($_GET['servicio_id']) ? (int)$_GET['servicio_id'] : 0;
$servicio_seleccionado = null;

if ($servicio_id > 0) {
    foreach ($servicios as $servicio) {
        if ($servicio['id'] == $servicio_id) {
            $servicio_seleccionado = $servicio;
            break;
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-4">Reservar Cita</h1>
        <p class="lead">Selecciona un servicio, fecha y hora para tu cita.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Formulario de Reserva</h5>
            </div>
            <div class="card-body">
                <form id="reserva-form" method="post" action="../api/citas.php">
                    <input type="hidden" name="csrf_token" value="<?php echo generar_token_csrf(); ?>">
                    
                    <!-- Paso 1: Selección de Servicio -->
                    <div id="paso-1" class="paso-reserva">
                        <h4 class="mb-3">Paso 1: Selecciona un Servicio</h4>
                        
                        <div class="mb-3">
                            <label for="servicio_id" class="form-label">Servicio</label>
                            <select class="form-select" id="servicio_id" name="servicio_id" required>
                                <option value="">Selecciona un servicio</option>
                                <?php foreach ($servicios as $servicio): ?>
                                    <option value="<?php echo $servicio['id']; ?>" <?php echo ($servicio_seleccionado && $servicio['id'] == $servicio_seleccionado['id']) ? 'selected' : ''; ?>>
                                        <?php echo sanitizar_input($servicio['nombre']); ?> (<?php echo $servicio['duracion']; ?> min)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Debes seleccionar un servicio</div>
                        </div>
                        
                        <div id="servicio-detalles" class="alert alert-info d-none">
                            <h5 id="servicio-nombre"></h5>
                            <p id="servicio-descripcion"></p>
                            <p class="mb-0"><strong>Duración:</strong> <span id="servicio-duracion"></span> minutos</p>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="button" id="btn-paso-2" class="btn btn-primary">Siguiente <i class="fas fa-arrow-right ms-1"></i></button>
                        </div>
                    </div>
                    
                    <!-- Paso 2: Selección de Fecha -->
                    <div id="paso-2" class="paso-reserva d-none">
                        <h4 class="mb-3">Paso 2: Selecciona una Fecha</h4>
                        
                        <div class="mb-3">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input type="text" class="form-control" id="fecha" name="fecha" placeholder="Selecciona una fecha" required readonly>
                            <div class="invalid-feedback">Debes seleccionar una fecha</div>
                        </div>
                        
                        <div id="calendario-container" class="mb-3"></div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" id="btn-volver-1" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Anterior</button>
                            <button type="button" id="btn-paso-3" class="btn btn-primary">Siguiente <i class="fas fa-arrow-right ms-1"></i></button>
                        </div>
                    </div>
                    
                    <!-- Paso 3: Selección de Hora -->
                    <div id="paso-3" class="paso-reserva d-none">
                        <h4 class="mb-3">Paso 3: Selecciona una Hora</h4>
                        
                        <div class="mb-3">
                            <label for="hora" class="form-label">Hora</label>
                            <select class="form-select" id="hora" name="hora" required disabled>
                                <option value="">Selecciona una hora</option>
                            </select>
                            <div class="invalid-feedback">Debes seleccionar una hora</div>
                        </div>
                        
                        <div id="horarios-container" class="mb-3">
                            <div class="alert alert-info">
                                <p class="mb-0">Selecciona una fecha para ver los horarios disponibles.</p>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" id="btn-volver-2" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Anterior</button>
                            <button type="button" id="btn-paso-4" class="btn btn-primary">Siguiente <i class="fas fa-arrow-right ms-1"></i></button>
                        </div>
                    </div>
                    
                    <!-- Paso 4: Confirmación -->
                    <div id="paso-4" class="paso-reserva d-none">
                        <h4 class="mb-3">Paso 4: Confirma tu Reserva</h4>
                        
                        <div class="alert alert-info mb-4">
                            <h5>Resumen de la Reserva</h5>
                            <p><strong>Servicio:</strong> <span id="resumen-servicio"></span></p>
                            <p><strong>Fecha:</strong> <span id="resumen-fecha"></span></p>
                            <p><strong>Hora:</strong> <span id="resumen-hora"></span></p>
                            <p><strong>Duración:</strong> <span id="resumen-duracion"></span> minutos</p>
                        </div>
                        
                        <div class="mb-3">
                            <label for="mensaje" class="form-label">Mensaje de Confirmación</label>
                            <textarea class="form-control" id="mensaje" name="mensaje" rows="3" readonly></textarea>
                            <div class="form-text">Este mensaje se generará automáticamente cuando confirmes tu reserva.</div>
                        </div>
                        
                        <div id="mensaje-generando" class="alert alert-warning d-none">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-2" role="status">
                                    <span class="visually-hidden">Generando...</span>
                                </div>
                                <div>Generando mensaje de confirmación...</div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" id="btn-volver-3" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Anterior</button>
                            <button type="submit" id="btn-confirmar" class="btn btn-success">Confirmar Reserva</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Información</h5>
            </div>
            <div class="card-body">
                <p>Para reservar una cita, sigue estos pasos:</p>
                <ol>
                    <li>Selecciona el servicio que deseas reservar.</li>
                    <li>Elige una fecha disponible en el calendario.</li>
                    <li>Selecciona un horario disponible.</li>
                    <li>Confirma tu reserva.</li>
                </ol>
                <p>Una vez confirmada, tu cita quedará en estado "pendiente" hasta que sea aprobada por un administrador.</p>
                <hr>
                <p class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i> Puedes cancelar tus citas pendientes desde la sección "Mis Citas".</p>
            </div>
        </div>
    </div>
</div>

<!-- Script para el proceso de reserva -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const servicioSelect = document.getElementById('servicio_id');
    const servicioDetalles = document.getElementById('servicio-detalles');
    const servicioNombre = document.getElementById('servicio-nombre');
    const servicioDescripcion = document.getElementById('servicio-descripcion');
    const servicioDuracion = document.getElementById('servicio-duracion');
    
    const fechaInput = document.getElementById('fecha');
    const horaSelect = document.getElementById('hora');
    const horariosContainer = document.getElementById('horarios-container');
    
    const resumenServicio = document.getElementById('resumen-servicio');
    const resumenFecha = document.getElementById('resumen-fecha');
    const resumenHora = document.getElementById('resumen-hora');
    const resumenDuracion = document.getElementById('resumen-duracion');
    const mensajeTextarea = document.getElementById('mensaje');
    const mensajeGenerando = document.getElementById('mensaje-generando');
    
    const paso1 = document.getElementById('paso-1');
    const paso2 = document.getElementById('paso-2');
    const paso3 = document.getElementById('paso-3');
    const paso4 = document.getElementById('paso-4');
    
    const btnPaso2 = document.getElementById('btn-paso-2');
    const btnPaso3 = document.getElementById('btn-paso-3');
    const btnPaso4 = document.getElementById('btn-paso-4');
    const btnVolver1 = document.getElementById('btn-volver-1');
    const btnVolver2 = document.getElementById('btn-volver-2');
    const btnVolver3 = document.getElementById('btn-volver-3');
    const btnConfirmar = document.getElementById('btn-confirmar');
    
    const reservaForm = document.getElementById('reserva-form');
    
    let servicios = <?php echo json_encode($servicios); ?>;
    
    let servicioSeleccionado = null;
    
    const calendarioFlatpickr = flatpickr('#calendario-container', {
        inline: true,
        minDate: 'today',
        dateFormat: 'Y-m-d',
        locale: 'es',
        disable: [
            function(date) {
                return date.getDay() === 0;
            }
        ],
        onChange: function(selectedDates, dateStr) {
            fechaInput.value = dateStr;
            cargarHorariosDisponibles(dateStr);
        }
    });
    
    servicioSelect.addEventListener('change', function() {
        const servicioId = parseInt(this.value);
        
        if (servicioId) {
            servicioSeleccionado = servicios.find(s => s.id === servicioId);
            
            if (servicioSeleccionado) {
                servicioNombre.textContent = servicioSeleccionado.nombre;
                servicioDescripcion.textContent = servicioSeleccionado.descripcion;
                servicioDuracion.textContent = servicioSeleccionado.duracion;
                servicioDetalles.classList.remove('d-none');
            } else {
                servicioDetalles.classList.add('d-none');
            }
        } else {
            servicioDetalles.classList.add('d-none');
            servicioSeleccionado = null;
        }
    });
    
    btnPaso2.addEventListener('click', function() {
        if (!servicioSeleccionado) {
            mostrarError(servicioSelect, 'Debes seleccionar un servicio');
            return;
        }
        
        paso1.classList.add('d-none');
        paso2.classList.remove('d-none');
    });
    
    btnVolver1.addEventListener('click', function() {
        paso2.classList.add('d-none');
        paso1.classList.remove('d-none');
    });
    
    btnPaso3.addEventListener('click', function() {
        if (!fechaInput.value) {
            mostrarError(fechaInput, 'Debes seleccionar una fecha');
            return;
        }
        
        paso2.classList.add('d-none');
        paso3.classList.remove('d-none');
    });
    
    btnVolver2.addEventListener('click', function() {
        paso3.classList.add('d-none');
        paso2.classList.remove('d-none');
    });
    
    btnPaso4.addEventListener('click', function() {
        if (!horaSelect.value) {
            mostrarError(horaSelect, 'Debes seleccionar una hora');
            return;
        }
        
        resumenServicio.textContent = servicioSeleccionado.nombre;
        resumenFecha.textContent = formatearFecha(fechaInput.value);
        resumenHora.textContent = horaSelect.value;
        resumenDuracion.textContent = servicioSeleccionado.duracion;
        
        generarMensajeConfirmacion();
        
        paso3.classList.add('d-none');
        paso4.classList.remove('d-none');
    });
    
    btnVolver3.addEventListener('click', function() {
        paso4.classList.add('d-none');
        paso3.classList.remove('d-none');
    });
    
    function cargarHorariosDisponibles(fecha) {
        if (!servicioSeleccionado) return;
        
        horariosContainer.innerHTML = `
            <div class="alert alert-info">
                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <div>Cargando horarios disponibles...</div>
                </div>
            </div>
        `;
        
        horaSelect.disabled = true;
        horaSelect.innerHTML = '<option value="">Cargando horarios...</option>';
        
        fetch(`../api/citas.php?action=available_slots&servicio_id=${servicioSeleccionado.id}&fecha=${fecha}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    horaSelect.disabled = false;
                    horaSelect.innerHTML = '<option value="">Selecciona una hora</option>';
                    
                    if (data.slots.length > 0) {
                        let horariosHTML = '<div class="time-slots">';
                        
                        data.slots.forEach(slot => {
                            const option = document.createElement('option');
                            option.value = slot;
                            option.textContent = slot;
                            horaSelect.appendChild(option);
                            
                            horariosHTML += `
                                <button type="button" class="btn btn-outline-primary time-slot-btn" data-hora="${slot}">
                                    ${slot}
                                </button>
                            `;
                        });
                        
                        horariosHTML += '</div>';
                        horariosContainer.innerHTML = horariosHTML;
                        
                        document.querySelectorAll('.time-slot-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const hora = this.getAttribute('data-hora');
                                horaSelect.value = hora;
                                
                                document.querySelectorAll('.time-slot-btn').forEach(b => {
                                    b.classList.remove('active');
                                });
                                
                                this.classList.add('active');
                            });
                        });
                    } else {
                        horariosContainer.innerHTML = `
                            <div class="alert alert-warning">
                                <p class="mb-0">No hay horarios disponibles para esta fecha. Por favor, selecciona otra fecha.</p>
                            </div>
                        `;
                    }
                } else {
                    horariosContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <p class="mb-0">Error al cargar horarios: ${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                horariosContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <p class="mb-0">Error de conexión. Inténtalo de nuevo más tarde.</p>
                    </div>
                `;
            });
    }
    
    function generarMensajeConfirmacion() {
        mensajeGenerando.classList.remove('d-none');
        mensajeTextarea.value = '';
        
        fetch('../api/citas.php?action=generate_confirmation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'csrf_token': document.querySelector('input[name="csrf_token"]').value,
                'servicio': servicioSeleccionado.nombre,
                'fecha': formatearFecha(fechaInput.value),
                'hora': horaSelect.value
            })
        })
        .then(response => response.json())
        .then(data => {
            mensajeGenerando.classList.add('d-none');
            
            if (data.success) {
                mensajeTextarea.value = data.message;
            } else {
                mensajeTextarea.value = `Tu cita para ${servicioSeleccionado.nombre} está programada el ${formatearFecha(fechaInput.value)} a las ${horaSelect.value}. ¡Gracias!`;
                console.error('Error:', data.message);
            }
        })
        .catch(error => {
            mensajeGenerando.classList.add('d-none');
            mensajeTextarea.value = `Tu cita para ${servicioSeleccionado.nombre} está programada el ${formatearFecha(fechaInput.value)} a las ${horaSelect.value}. ¡Gracias!`;
            console.error('Error:', error);
        });
    }
    
    reservaForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        btnConfirmar.disabled = true;
        btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';
        
        fetch(this.action, {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'mis-citas.php?success=1';
            } else {
                alert('Error: ' + data.message);
                btnConfirmar.disabled = false;
                btnConfirmar.textContent = 'Confirmar Reserva';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión. Inténtalo de nuevo más tarde.');
            btnConfirmar.disabled = false;
            btnConfirmar.textContent = 'Confirmar Reserva';
        });
    });
    
    function mostrarError(input, mensaje) {
        input.classList.add('is-invalid');
        const feedbackElement = input.nextElementSibling;
        if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
            feedbackElement.textContent = mensaje;
        }
    }
    
    function formatearFecha(fechaStr) {
        const fecha = new Date(fechaStr);
        return fecha.toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
    
    if (servicioSelect.value) {
        servicioSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<?php
require_once '../includes/footer.php';
?>
