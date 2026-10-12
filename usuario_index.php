<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

require_once('config/conexion.php');

$sql_carreras = "SELECT DISTINCT carrera FROM horarios ORDER BY carrera ASC";
$res_carreras = $conn->query($sql_carreras);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universidad Anáhuac | Registro de Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
    
    <!-- Librería QRCodeJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <script>
        const ROL_USUARIO = "usuario";
    </script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-comic sticky-top py-2" style="background-color: #0d6efd;">
    <div class="container">
        <a class="navbar-brand text-white title-comic d-flex align-items-center fs-4" href="usuario_index.php">
            <img src="logo2.png" alt="Logo Anáhuac" class="me-2" style="height: 40px; width: auto; object-fit: contain;">
            <i class="fa-solid fa-graduation-cap text-warning me-2"></i> REGISTRO DE ASISTENCIA
        </a>
        <div class="d-flex align-items-center gap-2 ms-auto">
            <span class="badge btn-comic btn-comic-yellow text-dark me-2">
                (◕‿◕) <?= strtoupper($_SESSION['usuario']) ?>
            </span>
            <a href="logout.php" class="btn btn-comic btn-comic-dark ms-2" title="Cerrar Sesión">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </div>
</nav>

<div class="container my-4">
    <div class="row g-4">
        
        <!-- Panel Izquierdo -->
        <div class="col-lg-3">
            <div class="card card-comic p-3 text-center mb-3">
                <h6 class="title-comic mb-1">HORARIO DEL SISTEMA</h6>
                <div id="reloj-digital" class="display-6 fw-bold text-dark my-1" style="font-family: 'Bebas Neue';">00:00:00</div>
                <small class="fw-bold border-top border-secondary pt-1 d-block text-muted"><?= date('d / m / Y') ?></small>
            </div>

            <div class="card card-comic p-3 mb-3">
                <h6 class="title-comic text-center mb-2"><i class="fa-solid fa-magnifying-glass me-1"></i> BUSCAR DOCENTE</h6>
                <input type="text" id="input-buscador" class="form-control form-control-sm text-center border-dark fw-bold" placeholder="Materia, docente o carrera...">
            </div>

            <div class="card card-comic p-3 mb-3">
                <h6 class="title-comic text-center mb-2"><i class="fa-solid fa-filter me-1"></i> FILTRAR CARRERA</h6>
                <select id="select-carrera" class="form-select form-select-sm border-dark fw-bold text-center" onchange="filtrarPorCarrera()">
                    <option value="">-- TODAS LAS CARRERAS --</option>
                    <?php while ($carrera = $res_carreras->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($carrera['carrera']) ?>"><?= htmlspecialchars($carrera['carrera']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="card card-comic p-3">
                <button class="btn btn-comic btn-comic-yellow w-100 mb-2" onclick="cargarEstadoDocentes()">
                    <i class="fa-solid fa-rotate me-1"></i> REFRESCAR LISTA
                </button>
            </div>
        </div>

        <!-- Panel Derecho: Tabla de Horarios -->
        <div class="col-lg-9">
            <div class="card card-comic p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-dark pb-2">
                    <h5 class="title-comic mb-0">HORARIOS Y CONTROL DE DOCENTES</h5>
                    <span class="badge bg-primary text-white rounded-0 border border-dark px-3 py-2">CHECADOR POR QR</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-comic align-middle text-center">
                        <thead>
                            <tr>
                                <th class="text-start">CARRERA</th>
                                <th class="text-start">CUATRIMESTRE</th>
                                <th class="text-start">DOCENTE</th>
                                <th class="text-start">ASIGNATURA</th>
                                <th>HORARIO</th>
                                <th>ESTATUS</th>
                                <th>ENTRADA</th>
                                <th>SALIDA</th>
                            </tr>
                        </thead>
                        <tbody id="contenedor-horarios-general">
                            <!-- Filas dinámicas desde app_usuario.js -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Escáner QR de Asistencia -->
<div class="modal fade" id="modalQR" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content card-comic p-3 text-center">
            <div class="modal-header border-bottom border-dark pb-2">
                <h5 class="title-comic mb-0"><i class="fa-solid fa-qrcode me-2"></i>CÓDIGO DE ESCANEO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3 d-flex flex-column align-items-center">
                <p class="fw-bold mb-1 text-primary" id="qr-docente-nombre">Docente</p>
                <small class="badge bg-dark mb-3" id="qr-docente-carrera">Carrera</small>
                
                <div id="qrcode" class="p-2 bg-white border border-dark mb-3 shadow-sm"></div>
                
                <small class="text-muted fw-bold">Escanea con tu teléfono para registrar.</small>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Motivo de Salida -->
<div class="modal fade" id="modalVerMotivo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content card-comic p-3 text-center">
            <div class="modal-header border-bottom border-dark pb-2">
                <h5 class="title-comic mb-0"><i class="fa-solid fa-clipboard-question me-2"></i>MOTIVO DE SALIDA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <p id="texto-docente-motivo" class="fw-bold text-primary mb-1"></p>
                <div class="alert alert-light border border-dark text-start small fw-bold mt-2" id="contenido-motivo"></div>
            </div>
        </div>
    </div>
</div>
<!-- MODAL DE CUESTIONARIO Y QR DE SALIDA -->
<div class="modal fade" id="modalSalidaQR" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-3 border-dark shadow-lg">
      <div class="modal-header bg-warning text-dark border-bottom border-dark">
        <h5 class="modal-title fw-bold text-uppercase" id="modalSalidaTitulo">Salida Anticipada - Registro</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="modalSalidaCuerpo">
        
        <!-- PASO 1: CUESTIONARIO -->
        <div id="paso-cuestionario">
          <p class="fw-bold text-danger small mb-3">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            Estás registrando tu salida antes del horario límite. Por favor selecciona el motivo:
          </p>
          
          <form id="formCuestionarioSalida">
            <input type="hidden" id="salida_id_horario">
            
            <div class="mb-3">
              <label class="form-label fw-bold small text-uppercase">Motivo Principal:</label>
              <select class="form-select border-dark" id="salida_motivo" required>
                <option value="">-- Selecciona una opción --</option>
                <option value="Asunto Personal / Familiar">Asunto Personal / Familiar</option>
                <option value="Motivo de Salud / Médica">Motivo de Salud / Médica</option>
                <option value="Comisión Académica / Institucional">Comisión Académica / Institucional</option>
                <option value="Fin de Temario / Examen Concluido">Fin de Temario / Examen Concluido</option>
                <option value="Falta de Alumnos">Falta de Alumnos</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold small text-uppercase">Especificación (Opción cerrada):</label>
              <select class="form-select border-dark" id="salida_detalle" required>
                <option value="">-- Selecciona el detalle --</option>
                <option value="Con permiso de dirección">Con permiso previo de dirección</option>
                <option value="Sin permiso previo">Sin permiso previo</option>
                <option value="Cita médica programada">Cita médica programada</option>
                <option value="Emergencia de salud">Emergencia de salud</option>
                <option value="Actividad autorizada en campus">Actividad autorizada en campus</option>
              </select>
            </div>

            <button type="submit" class="btn btn-warning border-dark w-100 fw-bold shadow-sm py-2">
              <i class="fa-solid fa-qrcode me-1"></i> GUARDAR Y GENERAR QR DE SALIDA
            </button>
          </form>
        </div>

        <!-- PASO 2: MOSTRAR QR GENERADO -->
        <div id="paso-qr-salida" class="text-center d-none">
          <h6 class="fw-bold text-uppercase mb-2" id="salida_docente_nombre"></h6>
          <p class="small text-muted mb-3">Escanea este código QR con el checador para confirmar la salida.</p>
          
          <div id="contenedorQRSalida" class="d-flex justify-content-center mb-3"></div>

          <div class="alert alert-info border-dark small fw-bold mb-0">
            <i class="fa-solid fa-info-circle me-1"></i> Cuestionario guardado exitosamente. Escanea para concluir.
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app_usuario.js?v=<?= time(); ?>"></script>

<script>
let qrMaker = null;

// INTERCEPTOR OBLIGATORIO DE SALIDA
function solicitarSalida(idHorario, nombreDocente) {
    // 1. Guardar el ID y nombre en el modal
    document.getElementById('salida_id_horario').value = idHorario;
    document.getElementById('salida_docente_nombre').innerText = nombreDocente;
    
    // 2. Limpiar campos del cuestionario
    document.getElementById('salida_motivo').value = '';
    document.getElementById('salida_detalle').value = '';

    // 3. Forzar mostrar Cuestionario y ocultar QR
    document.getElementById('paso-cuestionario').classList.remove('d-none');
    document.getElementById('paso-qr-salida').classList.add('d-none');
    
    // 4. Abrir el modal del Cuestionario
    const modalSalida = new bootstrap.Modal(document.getElementById('modalSalidaQR'));
    modalSalida.show();
}

// AL ENVIAR EL CUESTIONARIO (BOTÓN GUARDAR Y GENERAR QR)
document.getElementById('formCuestionarioSalida').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const idHorario = document.getElementById('salida_id_horario').value;
    const motivo = document.getElementById('salida_motivo').value;
    const detalle = document.getElementById('salida_detalle').value;

    const formData = new FormData();
    formData.append('id_horario', idHorario);
    formData.append('motivo', motivo);
    formData.append('detalle', detalle);

    // Guardar respuestas en BD por AJAX
    fetch('guardar_motivo_salida.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // Ocultar formulario y mostrar el contenedor del QR
            document.getElementById('paso-cuestionario').classList.add('d-none');
            document.getElementById('paso-qr-salida').classList.remove('d-none');

            // Generar el código QR
            const contenedorQR = document.getElementById('contenedorQRSalida');
            contenedorQR.innerHTML = '';
            
            const urlAbsoluta = window.location.origin + window.location.pathname.replace('usuario_index.php', '') + 'marcar_salida_qr.php?id=' + idHorario;

            new QRCode(contenedorQR, {
                text: urlAbsoluta,
                width: 200,
                height: 200,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        } else {
            alert('Error al guardar: ' + (data.message || 'Intente de nuevo.'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error al conectar con el servidor.');
    });
});

// Sobreescribir mostrarQRDocente para que si detecta 'salida', redireccione al Cuestionario
const mostrarQRDocenteOriginal = window.mostrarQRDocente;
window.mostrarQRDocente = function(idHorario, nombreDocente, carrera, tipo = 'entrada') {
    if (tipo === 'salida') {
        // Redirigir SIEMPRE al cuestionario primero
        solicitarSalida(idHorario, nombreDocente);
        return;
    }

    // Si es ENTRADA, mostrar QR normal
    document.getElementById('qr-docente-nombre').textContent = nombreDocente;
    document.getElementById('qr-docente-carrera').textContent = carrera + ' (' + tipo.toUpperCase() + ')';
    
    const urlAbsoluta = window.location.origin + window.location.pathname.replace('usuario_index.php', '') + 'marcar_qr.php?id=' + idHorario;
    const contenedorQR = document.getElementById("qrcode");
    contenedorQR.innerHTML = "";

    qrMaker = new QRCode(contenedorQR, {
        text: urlAbsoluta,
        width: 180,
        height: 180,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });

    const modal = new bootstrap.Modal(document.getElementById('modalQR'));
    modal.show();
};

function verMotivoSalida(docente, observaciones) {
    document.getElementById('texto-docente-motivo').textContent = docente;
    document.getElementById('contenido-motivo').textContent = observaciones || 'Salida registrada correctamente.';
    const modal = new bootstrap.Modal(document.getElementById('modalVerMotivo'));
    modal.show();
}

function filtrarPorCarrera() {
    const seleccion = document.getElementById('select-carrera').value.toLowerCase();
    const filas = document.querySelectorAll('#contenedor-horarios-general tr');

    filas.forEach(fila => {
        const textoCarrera = fila.querySelector('.col-carrera')?.textContent.toLowerCase() || '';
        if (seleccion === '' || textoCarrera.includes(seleccion)) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}
</script>

</body>
</html>