<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: usuario_index.php');
    exit;
}

require_once('config/conexion.php');

// Obtener la lista única de carreras
$sql_carreras = "SELECT DISTINCT carrera FROM horarios ORDER BY carrera ASC";
$res_carreras = $conn->query($sql_carreras);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universidad Anáhuac | Panel de Administración y Verificación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
    <script>
        const ROL_USUARIO = "admin";
    </script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-comic sticky-top py-2" style="background-color: #212529;">
    <div class="container">
        <a class="navbar-brand text-white title-comic d-flex align-items-center fs-4" href="index_admin.php">
            <img src="logo2.png" alt="Logo Anáhuac" class="me-2" style="height: 40px; width: auto; object-fit: contain;">
            <i class="fa-solid fa-user-shield text-warning me-2"></i> PANEL DE ADMINISTRACIÓN Y VERIFICACIÓN
        </a>
        <div class="d-flex align-items-center gap-2 ms-auto">
            <span class="badge btn-comic btn-comic-yellow text-dark me-2">
                ( ˘︹˘ ) <?= strtoupper($_SESSION['usuario']) ?>
            </span>
            <a href="logout.php" class="btn btn-comic btn-comic-dark ms-2" title="Cerrar Sesión">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </div>
</nav>

<div class="container my-4">
    <div class="row g-4">
        
        <!-- Panel Izquierdo: Controles del Administrador -->
        <div class="col-lg-3">
            <div class="card card-comic p-3 text-center mb-3">
                <h6 class="title-comic mb-1">HORARIO DEL SISTEMA</h6>
                <div id="reloj-digital" class="display-6 fw-bold text-dark my-1" style="font-family: 'Bebas Neue';">00:00:00</div>
                <small class="fw-bold border-top border-secondary pt-1 d-block text-muted"><?= date('d / m / Y') ?></small>
            </div>

            <div class="card card-comic p-3 mb-3">
                <h6 class="title-comic text-center mb-2"><i class="fa-solid fa-magnifying-glass me-1"></i> BUSCAR REGISTRO</h6>
                <input type="text" id="input-buscador" class="form-control form-control-sm text-center border-dark fw-bold" placeholder="Docente, materia o carrera...">
            </div>

            <div class="card card-comic p-3 mb-3">
                <h6 class="title-comic text-center mb-2"><i class="fa-solid fa-filter me-1"></i> FILTRAR CARRERA</h6>
                <select id="select-carrera" class="form-select form-select-sm border-dark fw-bold text-center" onchange="filtrarPorCarreraAdmin()">
                    <option value="">-- TODAS LAS CARRERAS --</option>
                    <?php if ($res_carreras && $res_carreras->num_rows > 0): ?>
                        <?php while ($carrera = $res_carreras->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($carrera['carrera']) ?>"><?= htmlspecialchars($carrera['carrera']) ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="card card-comic p-3">
                <h6 class="title-comic text-center mb-3">ACCIONES DE AUDITORÍA</h6>
                <!-- BOTÓN PARA AGREGAR DOCENTE / HORARIO -->
                <button class="btn btn-success border-dark fw-bold w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalAgregarHorario">
                    <i class="fa-solid fa-user-plus me-1"></i> AGREGAR DOCENTE
                </button>
                <button class="btn btn-comic btn-comic-yellow w-100 mb-2" onclick="cargarEstadoDocentes()">
                    <i class="fa-solid fa-rotate me-1"></i> REFRESCAR MONITOREO
                </button>
                <button class="btn btn-comic btn-comic-yellow w-100" data-bs-toggle="modal" data-bs-target="#modalHorario">
                    <i class="fa-solid fa-calendar-days me-1"></i> HORARIOS GENERALES
                </button>
            </div>
        </div>

        <!-- Panel Derecho: Tabla de Verificación de Asistencia de Docentes -->
        <div class="col-lg-9">
            <div class="card card-comic p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-dark pb-2">
                    <h5 class="title-comic mb-0">SUPERVISIÓN Y ESTADO EN TIEMPO REAL</h5>
                    <span class="badge bg-dark text-white rounded-0 border border-dark px-3 py-2">AUDITORÍA COMPLETA</span>
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
                                <th>ENTRADA</th>
                                <th>HORA SALIDA</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody id="contenedor-horarios-general">
                            <!-- Datos insertados vía AJAX desde app.js -->
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- Modal Agregar Horario / Docente -->
<div class="modal fade" id="modalAgregarHorario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-comic p-3">
            <div class="modal-header border-bottom border-dark">
                <h5 class="title-comic mb-0"><i class="fa-solid fa-user-plus me-2"></i> REGISTRAR DOCENTE Y HORARIO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-agregar-horario">
                    <div class="mb-3 text-start">
                        <label class="fw-bold">Nombre del Docente:</label>
                        <input type="text" id="add-docente" name="docente" class="form-control border-dark" placeholder="Ej: LIC. JUAN PÉREZ" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label class="fw-bold">Carrera:</label>
                        <input type="text" id="add-carrera" name="carrera" class="form-control border-dark" placeholder="Ej: DERECHO" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label class="fw-bold">Asignatura:</label>
                        <input type="text" id="add-asignatura" name="asignatura" class="form-control border-dark" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label class="fw-bold">Cuatrimestre / Semestre:</label>
                        <input type="text" id="add-cuatrimestre" name="cuatrimestre" class="form-control border-dark" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3 text-start">
                            <label class="fw-bold">Hora Inicio:</label>
                            <input type="time" id="add-hora-inicio" name="hora_inicio" class="form-control border-dark" required>
                        </div>
                        <div class="col-6 mb-3 text-start">
                            <label class="fw-bold">Hora Fin:</label>
                            <input type="time" id="add-hora-fin" name="hora_fin" class="form-control border-dark" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success border-dark w-100 mt-2 fw-bold">
                        <i class="fa-solid fa-plus me-1"></i> AGREGAR REGISTRO
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Horario / Docente -->
<div class="modal fade" id="modalEditarHorario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-comic p-3">
            <div class="modal-header border-bottom border-dark">
                <h5 class="title-comic mb-0"><i class="fa-solid fa-pen-to-square me-2"></i> EDITAR HORARIO Y DOCENTE</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-editar-horario">
                    <input type="hidden" id="edit-id-horario" name="id_horario">
                    
                    <div class="mb-3 text-start">
                        <label class="fw-bold">Nombre del Docente:</label>
                        <input type="text" id="edit-docente" name="docente" class="form-control border-dark" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label class="fw-bold">Carrera:</label>
                        <input type="text" id="edit-carrera" name="carrera" class="form-control border-dark" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label class="fw-bold">Asignatura:</label>
                        <input type="text" id="edit-asignatura" name="asignatura" class="form-control border-dark" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label class="fw-bold">Cuatrimestre / Semestre:</label>
                        <input type="text" id="edit-cuatrimestre" name="cuatrimestre" class="form-control border-dark" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3 text-start">
                            <label class="fw-bold">Hora Inicio:</label>
                            <input type="time" id="edit-hora-inicio" name="hora_inicio" class="form-control border-dark" required>
                        </div>
                        <div class="col-6 mb-3 text-start">
                            <label class="fw-bold">Hora Fin:</label>
                            <input type="time" id="edit-hora-fin" name="hora_fin" class="form-control border-dark" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-comic btn-comic-yellow w-100 mt-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> GUARDAR CAMBIOS
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Horario General Completo -->
<div class="modal fade" id="modalHorario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content card-comic p-3">
            <div class="modal-header border-bottom border-dark">
                <h4 class="title-comic mb-0"><i class="fa-solid fa-table me-2"></i> MATRIZ GENERAL DE HORARIOS</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-comic text-center">
                        <thead>
                            <tr>
                                <th>HORA</th>
                                <th>LUNES</th>
                                <th>MARTES</th>
                                <th>MIÉRCOLES</th>
                                <th>JUEVES</th>
                                <th>VIERNES</th>
                                <th>SÁBADO</th>
                            </tr>
                        </thead>
                        <tbody id="matriz-horario-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js?v=<?= time(); ?>"></script>

<script>
function filtrarPorCarreraAdmin() {
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