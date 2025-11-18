// Validar email
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Validar contraseña
function validarPassword(password, minLength = 8) {
    return password.length >= minLength;
}

// Validar que las contraseñas coincidan
function validarPasswordsCoinciden(password, confirmPassword) {
    return password === confirmPassword;
}

// Validar número positivo
function validarNumeroPositivo(numero) {
    return !isNaN(numero) && parseFloat(numero) > 0;
}

// Validar rango de número
function validarRango(numero, min, max) {
    const num = parseFloat(numero);
    return !isNaN(num) && num >= min && num <= max;
}

// Mostrar mensaje de error en campo
function mostrarErrorCampo(campo, mensaje) {
    const input = document.getElementById(campo);
    if (!input) return;
    
    input.classList.add('is-invalid');
    
    // Crear o actualizar mensaje de error
    let errorDiv = input.nextElementSibling;
    if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        input.parentNode.insertBefore(errorDiv, input.nextSibling);
    }
    errorDiv.textContent = mensaje;
}

// Limpiar error de campo
function limpiarErrorCampo(campo) {
    const input = document.getElementById(campo);
    if (!input) return;
    
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    
    const errorDiv = input.nextElementSibling;
    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
        errorDiv.textContent = '';
    }
}

// Limpiar todos los errores del formulario
function limpiarErroresFormulario(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('.is-invalid, .is-valid');
    inputs.forEach(input => {
        input.classList.remove('is-invalid', 'is-valid');
    });
    
    const errorDivs = form.querySelectorAll('.invalid-feedback');
    errorDivs.forEach(div => div.textContent = '');
}

// Validar formulario de registro
function validarFormularioRegistro(formId) {
    limpiarErroresFormulario(formId);
    
    let esValido = true;
    
    // Validar nombre
    const nombre = document.getElementById('nombre');
    if (nombre && nombre.value.trim() === '') {
        mostrarErrorCampo('nombre', 'El nombre es obligatorio');
        esValido = false;
    } else if (nombre) {
        limpiarErrorCampo('nombre');
    }
    
    // Validar apellido
    const apellido = document.getElementById('apellido');
    if (apellido && apellido.value.trim() === '') {
        mostrarErrorCampo('apellido', 'El apellido es obligatorio');
        esValido = false;
    } else if (apellido) {
        limpiarErrorCampo('apellido');
    }
    
    // Validar email
    const email = document.getElementById('email');
    if (email) {
        if (email.value.trim() === '') {
            mostrarErrorCampo('email', 'El email es obligatorio');
            esValido = false;
        } else if (!validarEmail(email.value)) {
            mostrarErrorCampo('email', 'El email no es válido');
            esValido = false;
        } else {
            limpiarErrorCampo('email');
        }
    }
    
    // Validar contraseña
    const password = document.getElementById('password');
    if (password) {
        if (password.value.trim() === '') {
            mostrarErrorCampo('password', 'La contraseña es obligatoria');
            esValido = false;
        } else if (!validarPassword(password.value, 8)) {
            mostrarErrorCampo('password', 'La contraseña debe tener al menos 8 caracteres');
            esValido = false;
        } else {
            limpiarErrorCampo('password');
        }
    }
    
    // Validar confirmación de contraseña
    const confirmPassword = document.getElementById('confirm_password');
    if (confirmPassword && password) {
        if (confirmPassword.value.trim() === '') {
            mostrarErrorCampo('confirm_password', 'Confirma tu contraseña');
            esValido = false;
        } else if (!validarPasswordsCoinciden(password.value, confirmPassword.value)) {
            mostrarErrorCampo('confirm_password', 'Las contraseñas no coinciden');
            esValido = false;
        } else {
            limpiarErrorCampo('confirm_password');
        }
    }
    
    return esValido;
}

// Validar formulario de login
function validarFormularioLogin(formId) {
    limpiarErroresFormulario(formId);
    
    let esValido = true;
    
    const email = document.getElementById('email');
    if (email) {
        if (email.value.trim() === '') {
            mostrarErrorCampo('email', 'El email es obligatorio');
            esValido = false;
        } else if (!validarEmail(email.value)) {
            mostrarErrorCampo('email', 'El email no es válido');
            esValido = false;
        } else {
            limpiarErrorCampo('email');
        }
    }
    
    const password = document.getElementById('password');
    if (password && password.value.trim() === '') {
        mostrarErrorCampo('password', 'La contraseña es obligatoria');
        esValido = false;
    } else if (password) {
        limpiarErrorCampo('password');
    }
    
    return esValido;
}

// Validar formulario de actividad
function validarFormularioActividad(formId) {
    limpiarErroresFormulario(formId);
    
    let esValido = true;
    
    // Validar título
    const titulo = document.getElementById('titulo');
    if (titulo && titulo.value.trim() === '') {
        mostrarErrorCampo('titulo', 'El título es obligatorio');
        esValido = false;
    } else if (titulo) {
        limpiarErrorCampo('titulo');
    }
    
    // Validar ponderación
    const ponderacion = document.getElementById('ponderacion');
    if (ponderacion) {
        const valor = parseFloat(ponderacion.value);
        if (isNaN(valor) || valor < 1 || valor > 100) {
            mostrarErrorCampo('ponderacion', 'La ponderación debe estar entre 1 y 100');
            esValido = false;
        } else {
            limpiarErrorCampo('ponderacion');
        }
    }
    
    // Validar fecha límite
    const fechaLimite = document.getElementById('fecha_limite');
    if (fechaLimite) {
        if (fechaLimite.value === '') {
            mostrarErrorCampo('fecha_limite', 'La fecha límite es obligatoria');
            esValido = false;
        } else {
            const fecha = new Date(fechaLimite.value);
            const ahora = new Date();
            if (fecha < ahora) {
                mostrarErrorCampo('fecha_limite', 'La fecha límite debe ser futura');
                esValido = false;
            } else {
                limpiarErrorCampo('fecha_limite');
            }
        }
    }
    
    return esValido;
}

// Confirmar acción
function confirmarAccion(mensaje) {
    return confirm(mensaje);
}

// Validación en tiempo real para email
document.addEventListener('DOMContentLoaded', function() {
    const emailInputs = document.querySelectorAll('input[type="email"]');
    
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() !== '' && !validarEmail(this.value)) {
                mostrarErrorCampo(this.id, 'El email no es válido');
            } else if (this.value.trim() !== '') {
                limpiarErrorCampo(this.id);
            }
        });
    });
    
    // Validación de contraseñas coincidentes
    const passwordConfirm = document.getElementById('confirm_password');
    const password = document.getElementById('password');
    
    if (passwordConfirm && password) {
        passwordConfirm.addEventListener('input', function() {
            if (this.value !== '' && password.value !== this.value) {
                mostrarErrorCampo('confirm_password', 'Las contraseñas no coinciden');
            } else if (this.value === password.value) {
                limpiarErrorCampo('confirm_password');
            }
        });
    }
});

// Prevenir envío de formulario si hay errores
function validarYEnviar(event, formId, tipoValidacion) {
    event.preventDefault();
    
    let esValido = false;
    
    switch(tipoValidacion) {
        case 'registro':
            esValido = validarFormularioRegistro(formId);
            break;
        case 'login':
            esValido = validarFormularioLogin(formId);
            break;
        case 'actividad':
            esValido = validarFormularioActividad(formId);
            break;
        default:
            esValido = true;
    }
    
    if (esValido) {
        document.getElementById(formId).submit();
    }
    
    return esValido;
}