let listaDocentes = [];

function actualizarRelojDigital() {
    const reloj = document.getElementById('reloj-digital');
    if (!reloj) return;

    const ahora = new Date();
    const hrs = String(ahora.getHours()).padStart(2, '0');
    const mins = String(ahora.getMinutes()).padStart(2, '0');
    const segs = String(ahora.getSeconds()).padStart(2, '0');

    reloj.textContent = `${hrs}:${mins}:${segs}`;
}

setInterval(actualizarRelojDigital, 1000);

document.addEventListener('DOMContentLoaded', () => {
    actualizarRelojDigital();
    cargarEstadoDocentes();

    // Buscador en tiempo real
    const buscador = document.getElementById('input-buscador');
    if (buscador) {
        buscador.addEventListener('input', (e) => {
            const termino = e.target.value.toLowerCase();
            const filas = document.querySelectorAll('#contenedor-horarios-general tr');

            filas.forEach(fila => {
                const texto = fila.textContent.toLowerCase();
                fila.style.display = texto.includes(termino) ? '' : 'none';
            });
        });
    }

    // Formulario para AGREGAR nuevo docente / horario
    const formAgregar = document.getElementById('form-agregar-horario');
    if (formAgregar) {
        formAgregar.addEventListener('submit', guardarNuevoHorario);
    }

    // Formulario para EDITAR docente / horario
    const formEditar = document.getElementById('form-editar-horario');
    if (formEditar) {
        formEditar.addEventListener('submit', guardarEdicionHorario);
    }
});

// Carga la lista principal con HORA DE SALIDA, ACCIÓN EDITAR Y ELIMINAR
function cargarEstadoDocentes() {
    const contenedor = document.getElementById('contenedor-horarios-general');
    if (!contenedor) return;

    contenedor.innerHTML = `<tr><td colspan="8" class="text-center py-4"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><br>Cargando información...</td></tr>`;

    fetch('obtener_horarios_admin.php')
        .then(res => res.json())
        .then(data => {
            contenedor.innerHTML = '';

            if (data.error) {
                contenedor.innerHTML = `<tr><td colspan="8" class="text-center text-danger fw-bold py-3"><i class="fa-solid fa-triangle-exclamation me-1"></i> ${data.error}</td></tr>`;
                return;
            }

            if (!data || data.length === 0) {
                contenedor.innerHTML = `<tr><td colspan="8" class="text-center fw-bold py-3">No hay horarios registrados.</td></tr>`;
                return;
            }

            let htmlFilas = '';

            data.forEach(item => {
                const hInicio = item.hora_inicio ? item.hora_inicio.substring(0, 5) : '00:00';
                const hFin = item.hora_fin ? item.hora_fin.substring(0, 5) : '00:00';
                const hSalida = item.hora_salida ? item.hora_salida.substring(0, 5) : '--:--';

                const nombreDocente = item.docente || item.profesor || item.nombre_docente || 'SIN DOCENTE';
                const nombreMateria = item.materia || item.asignatura || 'SIN ASIGNATURA';

                let badge = '<span class="badge bg-secondary">PENDIENTE</span>';
                const est = (item.estatus || '').toUpperCase();

                if (est.includes('TIEMPO') || est === 'A') {
                    badge = '<span class="badge bg-success">A TIEMPO</span>';
                } else if (est.includes('RETARDO') || est === 'R') {
                    badge = '<span class="badge bg-warning text-dark">RETARDO</span>';
                } else if (est.includes('FALTA') || est === 'F') {
                    badge = '<span class="badge bg-danger">FALTA</span>';
                }

                // Escapar comillas para pasar el objeto seguro al JSON
                const jsonStr = JSON.stringify(item).replace(/'/g, "&apos;");

                htmlFilas += `
                    <tr>
                        <td class="text-start fw-bold col-carrera">${item.carrera || ''}</td>
                        <td class="text-start">${item.cuatrimestre || ''}</td>
                        <td class="text-start fw-bold text-uppercase">${nombreDocente}</td>
                        <td class="text-start">${nombreMateria}</td>
                        <td><span class="badge bg-dark">${hInicio} - ${hFin}</span></td>
                        <td>${badge}</td>
                        <td><span class="fw-bold text-primary">${hSalida}</span></td>
                        <td>
                            <div class="btn-group gap-1">
                                <button class="btn btn-comic btn-comic-yellow btn-sm" onclick='abrirModalEditar(${jsonStr})' title="Editar Docente/Horario">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn btn-danger btn-sm border-dark" onclick="eliminarHorario(${item.id_horario}, '${nombreDocente.replace(/'/g, "\\'")}')" title="Eliminar Registro">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            contenedor.innerHTML = htmlFilas;
        })
        .catch(err => {
            console.error('Error:', err);
            contenedor.innerHTML = `<tr><td colspan="8" class="text-center text-danger fw-bold py-3"><i class="fa-solid fa-circle-exclamation me-1"></i> Error de comunicación con el servidor.</td></tr>`;
        });
}

// Abrir modal de edición poblando TODOS los campos (incluyendo Nombre del Docente y Carrera)
function abrirModalEditar(item) {
    const elemId = document.getElementById('edit-id-horario');
    const elemDocente = document.getElementById('edit-docente');
    const elemCarrera = document.getElementById('edit-carrera');
    const elemAsig = document.getElementById('edit-asignatura');
    const elemCuatri = document.getElementById('edit-cuatrimestre');
    const elemInicio = document.getElementById('edit-hora-inicio');
    const elemFin = document.getElementById('edit-hora-fin');

    if (elemId) elemId.value = item.id_horario || '';
    if (elemDocente) elemDocente.value = item.docente || item.profesor || item.nombre_docente || '';
    if (elemCarrera) elemCarrera.value = item.carrera || '';
    if (elemAsig) elemAsig.value = item.asignatura || item.materia || '';
    if (elemCuatri) elemCuatri.value = item.cuatrimestre || '';
    if (elemInicio) elemInicio.value = item.hora_inicio ? item.hora_inicio.substring(0, 5) : '';
    if (elemFin) elemFin.value = item.hora_fin ? item.hora_fin.substring(0, 5) : '';

    const modalElem = document.getElementById('modalEditarHorario');
    if (modalElem) {
        const modal = new bootstrap.Modal(modalElem);
        modal.show();
    }
}

// Guardar los datos de AGREGAR DOCENTE / HORARIO
function guardarNuevoHorario(e) {
    e.preventDefault();
    const formData = new FormData(e.target);

    fetch('agregar_docente.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success' || data.exito) {
            alert(data.message || 'Docente agregado con éxito.');
            const modalEl = document.getElementById('modalAgregarHorario');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
            
            e.target.reset();
            cargarEstadoDocentes();
        } else {
            alert('Error: ' + (data.message || data.error || 'No se pudo registrar.'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error al conectar con el servidor.');
    });
}

// Guardar los datos editados (Docente, carrera, materia, horas)
function guardarEdicionHorario(e) {
    e.preventDefault();
    const formData = new FormData(e.target);

    fetch('guardar_editar_horario.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success' || data.exito) {
            const modalEl = document.getElementById('modalEditarHorario');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();

            cargarEstadoDocentes();
        } else {
            alert('Error: ' + (data.message || data.mensaje || 'No se pudieron guardar los cambios.'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Ocurrió un error al intentar guardar los cambios.');
    });
}

// Eliminar docente/horario de la base de datos
function eliminarHorario(idHorario, nombreDocente = '') {
    if (!idHorario) {
        alert("Este registro no tiene un ID válido.");
        return;
    }
    
    const mensajeConfirmacion = nombreDocente 
        ? `¿Estás seguro de que deseas eliminar al docente "${nombreDocente}" y su horario?`
        : "¿Estás seguro de que deseas borrar este registro de horario?";

    if (confirm(mensajeConfirmacion)) {
        const formData = new FormData();
        formData.append('id_horario', idHorario);

        fetch('eliminar_horario.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' || data.success || data.exito) {
                cargarEstadoDocentes();
            } else {
                alert(data.message || data.error || "Error al borrar el registro");
            }
        })
        .catch(err => {
            console.error("Error al borrar:", err);
            alert("Error al conectar con el servidor para eliminar.");
        });
    }
}