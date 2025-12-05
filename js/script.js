// Sistema de Validación para Registro de CbNoticias
document.addEventListener('DOMContentLoaded', function() {

  const formulario = document.getElementById('registerForm');
  const botonEnviar = document.getElementById('submitBtn');
  const camposTexto = ['nombre', 'usuario', 'clave'];

  const patrones = {
    nombre: /^[a-záéíóúñA-ZÁÉÍÓÚÑ\s]+$/,
    usuario: /^[a-zA-Z0-9_]{3,20}$/,
    clave: /^.{6,}$/
  };

  // Mensajes de ayuda
  // Mensajes de ayuda
  const mensajes = {
    nombre: 'Solo letras y espacios',
    usuario: '3-20 caracteres (letras, números, guiones bajos)',
    clave: 'Mínimo 6 caracteres'
  };

  // Estado de validación de campos
  // Estado de validación de campos
  const estadoValido = {
    nombre: true,
    usuario: false,
    clave: false
  };

  

  function actualizarBotonEnviar() {
    const todosValidos = Object.values(estadoValido).every(Boolean);
    botonEnviar.disabled = !todosValidos;
  }

  function mostrarAyuda(campoId) {
    const elementoMsg = document.getElementById(campoId + 'Msg');
    if (elementoMsg && mensajes[campoId]) {
      elementoMsg.textContent = mensajes[campoId];
      elementoMsg.className = 'validation-box show neutral';
    }
  }

  function ocultarValidacion(campoId) {
    const elementoMsg = document.getElementById(campoId + 'Msg');
    if (elementoMsg) {
      elementoMsg.className = 'validation-box';
      elementoMsg.textContent = '';
    }
  }

  function validarCampo(campoId) {
    const campo = document.getElementById(campoId);
    if (!campo) return false;
    
    const elementoMsg = document.getElementById(campoId + 'Msg');
    const valor = campo.value.trim();
    
    // Manejar campos vacío
    if (valor === '') {
      // El teléfono es opcional
      if (campoId === 'telefono') {
        ocultarValidacion(campoId);
        estadoValido[campoId] = true;
        campo.className = '';
        actualizarBotonEnviar();
        return true;
      } else {
        ocultarValidacion(campoId);
        estadoValido[campoId] = false;
        campo.className = '';
        actualizarBotonEnviar();
        return false;
      }
    }
    
    // Validar contra patrón
    const esValido = patrones[campoId].test(valor);
    estadoValido[campoId] = esValido;
    
    if (esValido) {
      elementoMsg.textContent = '✓ ' + mensajes[campoId];
      elementoMsg.className = 'validation-box show valid';
      campo.className = 'success';
    } else {
      elementoMsg.textContent = '✗ ' + mensajes[campoId];
      elementoMsg.className = 'validation-box show invalid';
      campo.className = 'error';
    }
    
    actualizarBotonEnviar();
    return esValido;
  }

  // Configurar escuchadores de eventos
  camposTexto.forEach(campoId => {
    const campo = document.getElementById(campoId);
    if (!campo) return;
    
    // Al enfocar - mostrar ayuda
    campo.addEventListener('focus', () => mostrarAyuda(campoId));
    
    campo.addEventListener('blur', function() {
      if (this.value.trim() === '' && campoId !== 'telefono') {
        ocultarValidacion(campoId);
        estadoValido[campoId] = false;
        actualizarBotonEnviar();
      } else {
        validarCampo(campoId);
      }
    });
    
    campo.addEventListener('input', function() {
      if (campoId === 'telefono') {
        this.value = this.value.replace(/\D/g, '').substring(0, 10);
      }
      validarCampo(campoId);
    });
  });

  formulario.addEventListener('submit', function(e) {
    let todosValidos = true;
    
  
    camposTexto.forEach(campoId => {
      if (!validarCampo(campoId) && campoId !== 'telefono') {
        todosValidos = false;
      }
    });
    
    if (!todosValidos) {
      e.preventDefault();
      alert('Por favor completa todos los campos correctamente.');
      return false;
    }
  });

  actualizarBotonEnviar();
});