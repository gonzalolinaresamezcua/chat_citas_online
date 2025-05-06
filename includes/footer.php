    </div><!-- Fin del contenedor principal -->

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Sistema de Citas Online</h5>
                    <p>Una plataforma para gestionar citas de forma eficiente y sencilla.</p>
                </div>
                <div class="col-md-3">
                    <h5>Enlaces</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_PATH; ?>/index.php" class="text-white">Inicio</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/index.php#servicios" class="text-white">Servicios</a></li>
                        <?php if (!isset($sesion) || !$sesion['logged_in']): ?>
                            <li><a href="<?php echo BASE_PATH; ?>/index.php#login" class="text-white">Iniciar Sesión</a></li>
                            <li><a href="<?php echo BASE_PATH; ?>/index.php#registro" class="text-white">Registrarse</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contacto</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i>info@citas-online.com</li>
                        <li><i class="fas fa-phone me-2"></i>(123) 456-7890</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Sistema de Citas Online. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    
    <!-- Scripts personalizados -->
    <script>
        flatpickr.localize(flatpickr.l10n.es);
        
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>
