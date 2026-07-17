<h2><?= isset($evaluacion) ? 'Edición de Evaluación Final' : 'Creación de Evaluación Final' ?></h2>

<form class="form-cargarEvaluacion" action="<?= isset($evaluacion) ? '/editar-evaluacion' : '/agregar-evaluacion' ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id_curso" value="<?= htmlspecialchars($_GET['curso']) ?>">
    <?php if (isset($evaluacion)): ?>
        <input type="hidden" name="id_evaluacion" value="<?= htmlspecialchars($evaluacion['id']) ?>">
    <?php endif; ?>
    
    <div class="input-box mb-4">
        <label for="titulo-eval">Título de la Evaluación:</label>
        <input type="text" class="form-control" name="titulo" id="titulo-eval" placeholder="Ej: Evaluación Integradora Final" value="<?= isset($evaluacion) ? htmlspecialchars($evaluacion['titulo']) : '' ?>" required>
    </div>

    <section id="preguntas-container" class="preguntas-list-container"></section>

    <div class="form-actions-eval">
        <button type="button" class="btnCursoAdd btn-agregar-pregunta" onclick="agregarPregunta()">
            <i class="fa-solid fa-plus"></i> Agregar Pregunta
        </button>
        <button type="submit" class="boton-agregarEvaluacion btn-guardar-eval">
            <i class="fa-solid fa-floppy-disk"></i> Guardar Evaluación
        </button>
    </div>
</form>

<script>
    let preguntaIndex = 0;

    function agregarPregunta(datos = null) {
        const container = document.getElementById('preguntas-container');

        const enunciadoVal = datos ? (datos.enunciado || '') : '';
        const tipoVal = datos ? (datos.tipo || 'multiple-choice') : 'multiple-choice';

        const section = document.createElement('section');
        section.classList.add('pregunta-card');
        section.innerHTML = `
            <div class="pregunta-card-header">
                <h4>Pregunta #${preguntaIndex + 1}</h4>
                <button type="button" class="btn-remove-pregunta" onclick="eliminarPregunta(this)" title="Eliminar pregunta">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
            
            <div class="pregunta-card-body">
                <div class="input-box mb-3">
                    <label>Enunciado de la Pregunta:</label>
                    <input type="text" class="form-control" name="preguntas[${preguntaIndex}][enunciado]" placeholder="Ej: ¿Cuál es la sintaxis correcta para declarar una variable?" value="${enunciadoVal.replace(/"/g, '&quot;')}" required>
                </div>

                <div class="input-box mb-3">
                    <label>Tipo de Ejercicio:</label>
                    <select class="form-select" name="preguntas[${preguntaIndex}][tipo]" onchange="mostrarOpciones(this, ${preguntaIndex})">
                        <option value="multiple-choice" ${tipoVal === 'multiple-choice' ? 'selected' : ''}>Múltiple Opción</option>
                        <option value="ordenar" ${tipoVal === 'ordenar' ? 'selected' : ''}>Ordenar Elementos</option>
                        <option value="completar" ${tipoVal === 'completar' ? 'selected' : ''}>Completar Texto</option>
                    </select>
                </div>

                <div id="opciones-${preguntaIndex}" class="opciones-container-box"></div>
            </div>
        `;

        container.appendChild(section);
        mostrarOpciones(section.querySelector('select'), preguntaIndex, datos);
        preguntaIndex++;
    }

    function eliminarPregunta(button) {
        button.closest('.pregunta-card').remove();
        reindexarPreguntas();
    }

    function reindexarPreguntas() {
        const cards = document.querySelectorAll('.preguntas-list-container .pregunta-card');
        cards.forEach((card, index) => {
            card.querySelector('.pregunta-card-header h4').innerText = `Pregunta #${index + 1}`;
        });
    }

    function mostrarOpciones(select, index, datos = null) {
        const tipo = select.value;
        const container = document.getElementById(`opciones-${index}`);
        container.innerHTML = "";

        if (tipo === "multiple-choice") {
            container.innerHTML = `
                <div class="opciones-title"><i class="fa-solid fa-list-ul"></i> Opciones de Respuesta (Selecciona la correcta)</div>
                <div id="mc-options-list-${index}" class="opciones-mc-grid" style="display: flex; flex-direction: column; gap: 0.75rem;"></div>
                <button type="button" class="btnCursoAdd" style="margin-top: 0.75rem; width: auto; align-self: flex-start;" onclick="agregarOpcionMC(${index})">
                    <i class="fa-solid fa-plus"></i> Agregar Opción
                </button>
            `;
            if (datos && datos.opciones && datos.opciones.length > 0) {
                datos.opciones.forEach(opcion => {
                    agregarOpcionMC(index, opcion.texto, opcion.es_correcta);
                });
            } else {
                for (let i = 0; i < 3; i++) {
                    agregarOpcionMC(index);
                }
            }
        } else if (tipo === "ordenar") {
            container.innerHTML = `
                <div class="opciones-title"><i class="fa-solid fa-arrow-down-1-9"></i> Elementos a Ordenar (Define el orden correcto)</div>
                <div id="order-options-list-${index}" class="opciones-order-grid" style="display: flex; flex-direction: column; gap: 0.75rem;"></div>
                <button type="button" class="btnCursoAdd" style="margin-top: 0.75rem; width: auto; align-self: flex-start;" onclick="agregarOpcionOrdenar(${index})">
                    <i class="fa-solid fa-plus"></i> Agregar Elemento
                </button>
            `;
            if (datos && datos.opciones && datos.opciones.length > 0) {
                const sortedOptions = [...datos.opciones].sort((a, b) => (a.posicion_correcta || 0) - (b.posicion_correcta || 0));
                sortedOptions.forEach(opcion => {
                    agregarOpcionOrdenar(index, opcion.texto);
                });
            } else {
                for (let i = 0; i < 3; i++) {
                    agregarOpcionOrdenar(index);
                }
            }
        } else if (tipo === "completar") {
            const enunciadoVal = datos ? (datos.enunciado || '') : '';
            const respuestaVal = datos ? (datos.palabra_correcta || '') : '';
            container.innerHTML = `
                <div class="opciones-title"><i class="fa-solid fa-pen-to-square"></i> Completar espacios en blanco</div>
                <div class="opciones-completar-box">
                    <div class="input-box mb-3">
                        <label>Texto con espacio a completar (usa "_" para indicar el espacio en blanco):</label>
                        <input type="text" class="form-control" name="preguntas[${index}][opciones][0][enunciado]" placeholder="Ej: La etiqueta _ sirve para enlazar un archivo CSS" value="${enunciadoVal.replace(/"/g, '&quot;')}" required>
                    </div>
                    <div class="input-box">
                        <label>Palabra o Respuesta Correcta Esperada:</label>
                        <input type="text" class="form-control" name="preguntas[${index}][opciones][0][respuesta_correcta]" placeholder="Ej: link" value="${respuestaVal.replace(/"/g, '&quot;')}" required>
                    </div>
                </div>
            `;
        }
    }

    function agregarOpcionMC(qIndex, texto = "", esCorrecta = 0) {
        const list = document.getElementById(`mc-options-list-${qIndex}`);
        const row = document.createElement('div');
        row.className = 'opcion-field-row';
        row.style.display = 'flex';
        row.style.alignItems = 'center';
        row.style.gap = '1rem';
        row.style.width = '100%';

        row.innerHTML = `
            <div class="opcion-input-group" style="flex: 1; display: flex; align-items: center; gap: 0.5rem;">
                <span class="opcion-prefix"></span>
                <input type="text" class="form-control" placeholder="Escribe una opción" value="${texto.replace(/"/g, '&quot;')}" required style="flex: 1;">
            </div>
            <label class="radio-correcta-label" style="display: flex; align-items: center; gap: 0.25rem; white-space: nowrap; cursor: pointer; margin-bottom: 0;">
                <input type="radio" ${esCorrecta ? 'checked' : ''} required>
                <span class="radio-indicator"></span>
                <span class="radio-text">Correcta</span>
            </label>
            <button type="button" class="btn-remove-opcion" onclick="eliminarOpcionRow(this, ${qIndex}, 'mc')" title="Eliminar opción" style="background: transparent; border: none; color: var(--color-danger); cursor: pointer; padding: 0.5rem; font-size: 1.1rem; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;
        list.appendChild(row);
        reindexarOpcionesMC(qIndex);
    }

    function reindexarOpcionesMC(qIndex) {
        const list = document.getElementById(`mc-options-list-${qIndex}`);
        if (!list) return;
        const rows = list.children;
        const isSingleRadioChecked = Array.from(rows).some(row => row.querySelector('input[type="radio"]').checked);

        Array.from(rows).forEach((row, i) => {
            const char = String.fromCharCode(65 + i);
            row.querySelector('.opcion-prefix').innerText = char;

            const textInput = row.querySelector('input[type="text"]');
            textInput.name = `preguntas[${qIndex}][opciones][${i}][texto]`;
            textInput.placeholder = `Escribe la opción ${char}`;

            const radioInput = row.querySelector('input[type="radio"]');
            radioInput.name = `preguntas[${qIndex}][correcta]`;
            radioInput.value = i;

            if (i === 0 && !isSingleRadioChecked) {
                radioInput.checked = true;
            }

            const removeBtn = row.querySelector('.btn-remove-opcion');
            if (rows.length <= 2) {
                removeBtn.style.visibility = 'hidden';
            } else {
                removeBtn.style.visibility = 'visible';
            }
        });
    }

    function agregarOpcionOrdenar(qIndex, texto = "") {
        const list = document.getElementById(`order-options-list-${qIndex}`);
        const row = document.createElement('div');
        row.className = 'opcion-field-row';
        row.style.display = 'flex';
        row.style.alignItems = 'center';
        row.style.gap = '1rem';
        row.style.width = '100%';

        row.innerHTML = `
            <div class="opcion-input-group" style="flex: 1; display: flex; align-items: center; gap: 0.5rem;">
                <span class="opcion-prefix"></span>
                <input type="text" class="form-control" placeholder="Escribe el elemento" value="${texto.replace(/"/g, '&quot;')}" required style="flex: 1;">
            </div>
            <input type="hidden">
            <button type="button" class="btn-remove-opcion" onclick="eliminarOpcionRow(this, ${qIndex}, 'ordenar')" title="Eliminar elemento" style="background: transparent; border: none; color: var(--color-danger); cursor: pointer; padding: 0.5rem; font-size: 1.1rem; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;
        list.appendChild(row);
        reindexarOpcionesOrdenar(qIndex);
    }

    function reindexarOpcionesOrdenar(qIndex) {
        const list = document.getElementById(`order-options-list-${qIndex}`);
        if (!list) return;
        const rows = list.children;

        Array.from(rows).forEach((row, i) => {
            row.querySelector('.opcion-prefix').innerText = i + 1;

            const textInput = row.querySelector('input[type="text"]');
            textInput.name = `preguntas[${qIndex}][opciones][${i}][texto]`;
            textInput.placeholder = `Elemento en posición ${i + 1}`;

            const hiddenInput = row.querySelector('input[type="hidden"]');
            hiddenInput.name = `preguntas[${qIndex}][opciones][${i}][posicion]`;
            hiddenInput.value = i + 1;

            const removeBtn = row.querySelector('.btn-remove-opcion');
            if (rows.length <= 2) {
                removeBtn.style.visibility = 'hidden';
            } else {
                removeBtn.style.visibility = 'visible';
            }
        });
    }

    function eliminarOpcionRow(button, qIndex, tipo) {
        button.closest('.opcion-field-row').remove();
        if (tipo === 'mc') {
            reindexarOpcionesMC(qIndex);
        } else {
            reindexarOpcionesOrdenar(qIndex);
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        const form = document.querySelector('.form-cargarEvaluacion');
        if (form) {
            form.addEventListener('submit', function(e) {
                const questionCards = document.querySelectorAll('.preguntas-list-container .pregunta-card');
                if (questionCards.length === 0) {
                    e.preventDefault();
                    alert('⚠️ Debes agregar al menos una pregunta para poder guardar la evaluación.');
                }
            });
        }

        <?php if (isset($evaluacion) && !empty($evaluacion['preguntas'])): ?>
            const preguntasIniciales = <?= json_encode($evaluacion['preguntas']) ?>;
            preguntasIniciales.forEach(preg => {
                agregarPregunta(preg);
            });
        <?php else: ?>
            // Si es agregar, cargamos una pregunta por defecto
            agregarPregunta();
        <?php endif; ?>
    });
</script>