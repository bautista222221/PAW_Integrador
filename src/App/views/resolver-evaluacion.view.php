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
                        foreach ($preg['opciones'] as $opIndex => $opcion): ?>
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
                        foreach ($opciones as $opcion): ?>
                            <section>
                                <?= htmlspecialchars($opcion['texto']) ?>
                                <select name="respuestas[<?= $index ?>][<?= $opcion['id'] ?>]" required>
                                    <option value="">Seleccioná posición</option>
                                    <?php for ($i = 1; $i <= count($opciones); $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </section>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </fieldset>
            <?php endforeach; ?>

            <section>
                <button class="btn-enviar-resp" type="submit">Enviar respuestas</button>
            </section>
        </form>
    </main>
    <?php include "parts/footer.php" ?>
</body>