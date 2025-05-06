/**
 * Script para manejar la autenticación de usuarios
 * Incluye validación en tiempo real y envío de formularios mediante AJAX
 */

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const registroForm = document.getElementById('registro-form');
    
    const loginError = document.getElementById('login-error');
    const loginSuccess = document.getElementById('login-success');
    const registroError = document.getElementById('registro-error');
    const registroSuccess = document.getElementById('registro-success');
    
    function mostrarError(input, mensaje) {
        input.classList.add('is-invalid');
        const feedbackElement = input.nextElementSibling;
        if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
            feedbackElement.textContent = mensaje;
        }
    }
    
    function limpiarError(input) {
        input.classList.remove('is-invalid');
        const feedbackElement = input.nextElementSibling;
        if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
            feedbackElement.textContent = '';
        }
    }
    
    function mostrarAlerta(elemento, mensaje, tipo = 'error') {
        elemento.textContent = mensaje;
        elemento.classList.remove('d-none');
        
        if (tipo === 'success') {
            elemento.classList.remove('alert-danger');
            elemento.classList.add('alert-success');
        } else {
            elemento.classList.remove('alert-success');
            elemento.classList.add('alert-danger');
        }
    }
    
    function ocultarAlerta(elemento) {
        elemento.classList.add('d-none');
        elemento.textContent = '';
    }
    
    if (loginForm) {
        const loginEmail = document.getElementById('login-email');
        const loginPassword = document.getElementById('login-password');
        
        loginEmail.addEventListener('input', function() {
            if (this.value.trim() === '') {
                mostrarError(this, 'El email es obligatorio');
            } else if (!isValidEmail(this.value)) {
                mostrarError(this, 'El email no es válido');
            } else {
                limpiarError(this);
            }
        });
        
        loginPassword.addEventListener('input', function() {
            if (this.value.trim() === '') {
                mostrarError(this, 'La contraseña es obligatoria');
            } else {
                limpiarError(this);
            }
        });
        
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            ocultarAlerta(loginError);
            ocultarAlerta(loginSuccess);
            
            let isValid = true;
            
            if (loginEmail.value.trim() === '') {
                mostrarError(loginEmail, 'El email es obligatorio');
                isValid = false;
            } else if (!isValidEmail(loginEmail.value)) {
                mostrarError(loginEmail, 'El email no es válido');
                isValid = false;
            }
            
            if (loginPassword.value.trim() === '') {
                mostrarError(loginPassword, 'La contraseña es obligatoria');
                isValid = false;
            }
            
            if (!isValid) {
                return;
            }
            
            const submitButton = loginForm.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';
            
            const formData = new FormData(loginForm);
            
            fetch(loginForm.action, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta(loginSuccess, data.message, 'success');
                    
                    setTimeout(function() {
                        if (data.user && data.user.rol === 'admin') {
                            window.location.href = 'admin/index.php';
                        } else {
                            window.location.href = 'cliente/index.php';
                        }
                    }, 1500);
                } else {
                    mostrarAlerta(loginError, data.message);
                    
                    if (data.errors) {
                        for (const field in data.errors) {
                            const input = document.getElementById('login-' + field);
                            if (input) {
                                mostrarError(input, data.errors[field]);
                            }
                        }
                    }
                }
            })
            .catch(error => {
                mostrarAlerta(loginError, 'Error al procesar la solicitud. Inténtalo de nuevo más tarde.');
                console.error('Error:', error);
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        });
    }
    
    if (registroForm) {
        const registroNombre = document.getElementById('registro-nombre');
        const registroEmail = document.getElementById('registro-email');
        const registroPassword = document.getElementById('registro-password');
        const registroPasswordConfirm = document.getElementById('registro-password-confirm');
        
        registroNombre.addEventListener('input', function() {
            if (this.value.trim() === '') {
                mostrarError(this, 'El nombre es obligatorio');
            } else {
                limpiarError(this);
            }
        });
        
        registroEmail.addEventListener('input', function() {
            if (this.value.trim() === '') {
                mostrarError(this, 'El email es obligatorio');
            } else if (!isValidEmail(this.value)) {
                mostrarError(this, 'El email no es válido');
            } else {
                limpiarError(this);
                
                verificarEmailDisponible(this.value);
            }
        });
        
        registroPassword.addEventListener('input', function() {
            if (this.value.trim() === '') {
                mostrarError(this, 'La contraseña es obligatoria');
            } else if (this.value.length < 8) {
                mostrarError(this, 'La contraseña debe tener al menos 8 caracteres');
            } else {
                limpiarError(this);
            }
            
            if (registroPasswordConfirm.value.trim() !== '') {
                if (this.value !== registroPasswordConfirm.value) {
                    mostrarError(registroPasswordConfirm, 'Las contraseñas no coinciden');
                } else {
                    limpiarError(registroPasswordConfirm);
                }
            }
        });
        
        registroPasswordConfirm.addEventListener('input', function() {
            if (this.value.trim() === '') {
                mostrarError(this, 'Debes confirmar la contraseña');
            } else if (this.value !== registroPassword.value) {
                mostrarError(this, 'Las contraseñas no coinciden');
            } else {
                limpiarError(this);
            }
        });
        
        registroForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            ocultarAlerta(registroError);
            ocultarAlerta(registroSuccess);
            
            let isValid = true;
            
            if (registroNombre.value.trim() === '') {
                mostrarError(registroNombre, 'El nombre es obligatorio');
                isValid = false;
            }
            
            if (registroEmail.value.trim() === '') {
                mostrarError(registroEmail, 'El email es obligatorio');
                isValid = false;
            } else if (!isValidEmail(registroEmail.value)) {
                mostrarError(registroEmail, 'El email no es válido');
                isValid = false;
            }
            
            if (registroPassword.value.trim() === '') {
                mostrarError(registroPassword, 'La contraseña es obligatoria');
                isValid = false;
            } else if (registroPassword.value.length < 8) {
                mostrarError(registroPassword, 'La contraseña debe tener al menos 8 caracteres');
                isValid = false;
            }
            
            if (registroPasswordConfirm.value.trim() === '') {
                mostrarError(registroPasswordConfirm, 'Debes confirmar la contraseña');
                isValid = false;
            } else if (registroPasswordConfirm.value !== registroPassword.value) {
                mostrarError(registroPasswordConfirm, 'Las contraseñas no coinciden');
                isValid = false;
            }
            
            if (!isValid) {
                return;
            }
            
            const submitButton = registroForm.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';
            
            const formData = new FormData(registroForm);
            
            fetch(registroForm.action, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta(registroSuccess, data.message, 'success');
                    
                    registroForm.reset();
                    
                    setTimeout(function() {
                        mostrarAlerta(registroSuccess, 'Registro exitoso. Ahora puedes iniciar sesión con tus credenciales.', 'success');
                    }, 3000);
                } else {
                    mostrarAlerta(registroError, data.message);
                    
                    if (data.errors) {
                        for (const field in data.errors) {
                            const input = document.getElementById('registro-' + field);
                            if (input) {
                                mostrarError(input, data.errors[field]);
                            }
                        }
                    }
                }
            })
            .catch(error => {
                mostrarAlerta(registroError, 'Error al procesar la solicitud. Inténtalo de nuevo más tarde.');
                console.error('Error:', error);
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        });
    }
    
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function verificarEmailDisponible(email) {
        
        if (!isValidEmail(email)) {
            return;
        }
        
    }
});
