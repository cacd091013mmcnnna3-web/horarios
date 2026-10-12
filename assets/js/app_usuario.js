document.addEventListener("DOMContentLoaded", () => {
    actualizarReloj();
    setInterval(actualizarReloj, 1000);
    cargarEstadoDocentes();

    // Filtro por buscador
    const inputBuscador = document.getElementById('input-buscador');
    if (inputBuscador) {
        inputBuscador.addEventListener('keyup', () => {
            const filtro = inputBuscador.value.toLowerCase();
            const filas = document.querySelectorAll('#contenedor-horarios-general tr');
            
            filas.forEach(fila => {
                const texto = fila.textContent.toLowerCase();
                fila.style.display = texto.includes(filtro) ? '' : 'none';
            });
        });
    }
});

function actualizarReloj() {
    const ahora = new Date();
    const horas = String(ahora.getHours()).padStart(2, '0');
    const minutos = String(ahora.getMinutes()).padStart(2, '0');
    const segundos = String(ahora.getSeconds()).padStart(2, '0');
    const elReloj = document.getElementById('reloj-digital');
    if (elReloj) {
        elReloj.textContent = `${horas}:${minutos}:${segundos}`;
    }
}

function cargarEstadoDocentes() {
    fetch('obtener_horarios_usuario.php')
        .then(response => response.json())
        .then(data => {
            const contenedor = document.getElementById('contenedor-horarios-general');
            if (!contenedor) return;

            let htmlFilas = '';

            if (data.error) {
                contenedor.innerHTML = `<tr><td colspan="8" class="text-danger fw-bold">${data.error}</td></tr>`;
                return;
            }

            if (data.length === 0) {
                contenedor.innerHTML = `<tr><td colspan="8" class="text-muted fw-bold">No hay horarios registrados.</td></tr>`;
                return;
            }

            data.forEach(item => {
                const nombreDocente = item.docente || 'SIN DOCENTE';
                const nombreMateria = item.asignatura || 'SIN ASIGNATURA';
                const hInicio = item.hora_inicio ? item.hora_inicio.substring(0, 5) : '00:00';
                const hFin = item.hora_fin ? item.hora_fin.substring(0, 5) : '00:00';

                // Estatus
                let badgeClass = 'bg-secondary';
                if (item.estatus === 'A TIEMPO') badgeClass = 'bg-success';
                else if (item.estatus === 'RETARDO') badgeClass = 'bg-warning text-dark';
                else if (item.estatus === 'FALTA') badgeClass = 'bg-danger';

                const badge = `<span class="badge ${badgeClass} text-uppercase px-2 py-1">${item.estatus}</span>`;

                // Botón de Entrada
                const btnEntrada = `
                    <button class="btn btn-comic btn-comic-yellow btn-sm" onclick="mostrarQRDocente(${item.id_horario}, '${nombreDocente.replace(/'/g, "\\'")}', '${(item.carrera || '').replace(/'/g, "\\'")}', 'entrada')">
                        <i class="fa-solid fa-qrcode me-1"></i> ENTRADA
                    </button>`;
                    let btnSalida = '';
                    if (item.hora_salida) {
                        btnSalida = `
                            <button class="btn btn-outline-dark btn-sm fw-bold" onclick="verMotivoSalida('${nombreDocente.replace(/'/g, "\\'")}', '${(item.observaciones || '').replace(/'/g, "\\'")}')">
                                <i class="fa-solid fa-clock me-1"></i> ${item.hora_salida.substring(0, 5)}
                            </button>`;
                    } else {
                        btnSalida = `
                            <button class="btn btn-comic btn-comic-dark btn-sm text-white" onclick="solicitarSalida(${item.id_horario}, '${nombreDocente.replace(/'/g, "\\'")}', '${item.hora_fin}')">
                                <i class="fa-solid fa-door-open me-1"></i> SALIDA
                            </button>`;
                    }

                htmlFilas += `
                    <tr>
                        <td class="text-start fw-bold col-carrera">${item.carrera || ''}</td>
                        <td class="text-start">${item.cuatrimestre || ''}</td>
                        <td class="text-start fw-bold text-uppercase">${nombreDocente}</td>
                        <td class="text-start">${nombreMateria}</td>
                        <td><span class="badge bg-dark">${hInicio} - ${hFin}</span></td>
                        <td>${badge}</td>
                        <td>${btnEntrada}</td>
                        <td>${btnSalida}</td>
                    </tr>
                `;
            });

            contenedor.innerHTML = htmlFilas;
        })
        .catch(err => {
            console.error('Error al cargar horarios:', err);
        });
}
function solicitarSalida(idHorario, nombreDocente, horaFinStr) {
    const ahora = new Date();
    const horaActualStr = ahora.getHours().toString().padStart(2, '0') + ':' + 
                          ahora.getMinutes().toString().padStart(2, '0') + ':' + 
                          ahora.getSeconds().toString().padStart(2, '0');
    
    // Asegurar formato de 8 caracteres "HH:MM:SS" en la hora limite
    let hFin = horaFinStr;
    if (hFin.length === 5) hFin += ':00';

    console.log("Hora actual:", horaActualStr, "Hora fin clase:", hFin);

    // Si la hora actual es MENOR a la hora fin, exige el cuestionario
    if (horaActualStr < hFin) {
        document.getElementById('salida_id_horario').value = idHorario;
        document.getElementById('salida_docente_nombre').innerText = nombreDocente;
        
        // Limpiar selects del cuestionario
        document.getElementById('salida_motivo').value = '';
        document.getElementById('salida_detalle').value = '';

        // Mostrar cuestionario y ocultar QR en el modal
        document.getElementById('paso-cuestionario').classList.remove('d-none');
        document.getElementById('paso-qr-salida').classList.add('d-none');
        
        const modal = new bootstrap.Modal(document.getElementById('modalSalidaQR'));
        modal.show();
    } else {
        // Si ya pasó la hora fin, muestra el QR directo sin cuestionario
        mostrarQRDocente(idHorario, nombreDocente, '', 'salida');
    }
}

// Evento al enviar el formulario del Cuestionario
document.getElementById('formCuestionarioSalida').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const idHorario = document.getElementById('salida_id_horario').value;
    const motivo = document.getElementById('salida_motivo').value;
    const detalle = document.getElementById('salida_detalle').value;

    const formData = new FormData();
    formData.append('id_horario', idHorario);
    formData.append('motivo', motivo);
    formData.append('detalle', detalle);

    fetch('guardar_motivo_salida.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // Ocultar cuestionario y mostrar el QR dentro del mismo modal
            document.getElementById('paso-cuestionario').classList.add('d-none');
            document.getElementById('paso-qr-salida').classList.remove('d-none');

            // Generar el código QR exactamente igual que en la entrada
            const contenedorQR = document.getElementById('contenedorQRSalida');
            contenedorQR.innerHTML = '';
            
            // La URL que leerá la cámara checadora
            const urlQR = window.location.origin + '/horarios/marcar_salida_qr.php?id=' + idHorario;

            new QRCode(contenedorQR, {
                text: urlQR,
                width: 200,
                height: 200
            });
        } else {
            alert('Error al guardar el motivo: ' + data.message);
        }
    })
    .catch(err => console.error('Error:', err));
}); 