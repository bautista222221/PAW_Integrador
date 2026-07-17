<?php include "parts/head.php" ?>

<body>
    <?php include "parts/header.php" ?>
    <main>
        <form class="form-evalucion" action="/resolver-evaluacion" method="POST">
            <!-- Curso asociado a la evaluación -->
            <input type="hidden" name="id_curso" value="<?= htmlspecialchars($_GET['curso'] ?? '') ?>">

            <?php foreach ($evaluacion['preguntas'] as $index => $preg): ?>
                <fieldset class="mb-4">
                    <legend>
                        <strong>Pregunta <?= $index + 1 ?> (<?= ucfirst($preg['tipo']) ?>):</strong>
                        <?= htmlspecialchars($preg['enunciado']) ?>
                    </legend>

                    <?php
                    if ($preg['tipo'] === 'multiple-choice'):
                        $opciones = $preg['opciones'];
                        shuffle($opciones);
                        foreach ($opciones as $opcion): ?>
                            <label>
                                <input type="radio" name="respuestas[<?= $index ?>]"
                                    value="<?= htmlspecialchars($opcion['id']) ?>" required>
                                <?= htmlspecialchars($opcion['texto']) ?>
                            </label><br>
                        <?php endforeach; ?>

                    <?php elseif ($preg['tipo'] === 'completar'): ?>
                        <p>
                            <?php
                            // El enunciado de tipo completar está directamente en $preg['enunciado'], con _ como espacio para completar
                            $oracion = htmlspecialchars($preg['enunciado']);
                            $input = '<input type="text" name="respuestas[' . $index . ']" placeholder="Tu respuesta" class="form-control d-inline-block" style="display:inline-block; width:auto; max-width:200px; margin:0 0.5rem;" required>';
                            echo preg_replace('/_+/', $input, $oracion);
                            ?>
                        </p>


                    <?php elseif ($preg['tipo'] === 'ordenar'):
                        // Barajar opciones para que aparezcan en orden aleatorio
                        $opciones = $preg['opciones'];
                        shuffle($opciones);
                        ?>
                        <div class="ordenar-container">
                            <p class="ordenar-instruccion"><i class="fa-solid fa-arrows-up-down"></i> Arrastrá los elementos para ordenarlos de arriba hacia abajo (el de más arriba es la posición 1):</p>
                            <ul class="sortable-list">
                                <?php foreach ($opciones as $opIndex => $opcion): ?>
                                    <li class="sortable-item" draggable="true" data-id="<?= $opcion['id'] ?>">
                                        <div class="item-handle">
                                            <i class="fa-solid fa-grip-lines"></i>
                                        </div>
                                        <span class="item-text"><?= htmlspecialchars($opcion['texto']) ?></span>
                                        <input type="hidden" name="respuestas[<?= $index ?>][<?= $opcion['id'] ?>]" class="input-orden" value="<?= $opIndex + 1 ?>">
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </fieldset>
            <?php endforeach; ?>

            <section>
                <button class="btn-enviar-resp" type="submit">Enviar respuestas</button>
            </section>
        </form>
    </main>
    <?php include "parts/footer.php" ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const lists = document.querySelectorAll('.sortable-list');
            
            lists.forEach(list => {
                let draggedItem = null;

                // Soporte para PC (Drag & Drop tradicional)
                list.addEventListener('dragstart', (e) => {
                    const item = e.target.closest('.sortable-item');
                    if (!item) return;
                    draggedItem = item;
                    item.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                });

                list.addEventListener('dragend', (e) => {
                    const item = e.target.closest('.sortable-item');
                    if (item) {
                        item.classList.remove('dragging');
                    }
                    draggedItem = null;
                    actualizarOrdenInputs(list);
                });

                list.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    const afterElement = getDragAfterElement(list, e.clientY);
                    if (afterElement == null) {
                        list.appendChild(draggedItem);
                    } else {
                        list.insertBefore(draggedItem, afterElement);
                    }
                });
            });

            function getDragAfterElement(container, y) {
                const draggableElements = [...container.querySelectorAll('.sortable-item:not(.dragging)')];

                return draggableElements.reduce((closest, child) => {
                    const box = child.getBoundingClientRect();
                    const offset = y - box.top - box.height / 2;
                    if (offset < 0 && offset > closest.offset) {
                        return { offset: offset, element: child };
                    } else {
                        return closest;
                    }
                }, { offset: Number.NEGATIVE_INFINITY }).element;
            }

            function actualizarOrdenInputs(list) {
                const items = list.querySelectorAll('.sortable-item');
                items.forEach((item, index) => {
                    const input = item.querySelector('.input-orden');
                    if (input) {
                        input.value = index + 1;
                    }
                });
            }
        });
    </script>
</body>