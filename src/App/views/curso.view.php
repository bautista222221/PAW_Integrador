<?php include "parts/head.php"; ?>

<body>
    <?php include "parts/header.php"; ?>
    <main class="curso-detalle">
        <?php if (isset($curso) && is_object($curso) && isset($curso->campos)): ?>
            <h2 class= "titulo-curso-principal"><?= htmlspecialchars($curso->campos['titulo']) ?></h2>
            <div class="curso-detalle-img-wrapper" style="width: 100%; height: 320px; overflow: hidden; border-radius: var(--radius-md); box-shadow: var(--shadow-md); margin-bottom: 2rem;">
                <?php if (!empty($curso->campos['imagen'])): ?>
                    <img src="<?= htmlspecialchars($curso->campos['imagen']) ?>" alt="Imagen de <?= htmlspecialchars($curso->campos['titulo']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <img src="/images/portadaCurso.jpg" alt="Imagen de portada" style="width: 100%; height: 100%; object-fit: cover;">
                <?php endif; ?>
            </div>
            <section class="curso-box">
                <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($curso->campos['descripcion'])) ?></p>
                <p><strong>Temario:</strong></p>
                <ul>
                    <?php foreach ($temas as $tema): ?>
                        <li><?= htmlspecialchars($tema["titulo"]) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p><strong>Nivel:</strong> <?= htmlspecialchars($curso->campos['nivel']) ?></p>
                <p><strong>Duración:</strong> <?= htmlspecialchars($curso->campos['duracion']) ?></p>
            </section>
            <?php if (!$inscripto): ?>
                <form action="/inscribirse" method="POST" class="form-inscripcion">
                    <input type="hidden" name="curso_id" value="<?= htmlspecialchars($curso->campos['id']) ?>">
                    <button type="submit" class="btn-inscribirse"><i class="fa-solid fa-user-plus"></i> Inscribirse al Curso</button>
                </form>
            <?php else: ?>
                <div class="estado-inscripcion-badge">
                    <span class="badge-success"><i class="fa-solid fa-circle-check"></i> Ya estás inscripto en este curso</span>
                </div>
            <?php endif; ?>
            <section class="unidades-box">
                <h3 class="curso-subt">Unidades</h3>
                <ul>
                    <?php foreach ($modulos as $modulo): ?>
                        <li class="curso-card">
                            <?php if ($inscripto): ?>
                                <a href="/ver-modulo?modulo=<?= urlencode($modulo['id']) ?>">
                                    <?= htmlspecialchars($modulo['titulo']) ?>
                                </a>
                            <?php else: ?>
                                <span class="modulo-bloqueado">
                                    <i class="fa-solid fa-lock"></i> <?= htmlspecialchars($modulo['titulo']) ?>
                                </span>
                            <?php endif; ?>
                            <p><?= htmlspecialchars($modulo['descripcion']) ?></p>
                            <?php if ($inscripto): ?>
                                <?php if (!empty($modulo['completado'])): ?>
                                    <p class="estado-modulo completado">✅ Completado</p>
                                <?php else: ?>
                                    <p class="estado-modulo incompleto">❌ No completado</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <!-- Sección de Evaluación Final (Solo visible si está matriculado) -->
            <?php if ($inscripto && $tieneEvaluacion): ?>
                <section class="curso-evaluacion-container">
                    <h3><i class="fa-solid fa-graduation-cap"></i> Evaluación Final</h3>
                    <p>Demuestra lo aprendido en este curso resolviendo el examen final.</p>
                    <a class="btn-resolver" href="/resolver-evaluacion?curso=<?= urlencode($curso->campos['id']) ?>">Resolver Evaluación</a>
                </section>
            <?php endif; ?>

            <!-- Controles de Administración (Solo admin) -->
            <?php if ($permiso): ?>
                <section class="curso-admin-actions mb-5">
                    <h3><i class="fa-solid fa-screwdriver-wrench"></i> Controles del Administrador</h3>
                    <div class="admin-buttons-grid">
                        <a class="btn-resolver" href="/editar-curso?id=<?= urlencode($curso->campos['id']) ?>"><i class="fa-solid fa-pen-to-square"></i> Editar Curso</a>
                        <?php if ($tieneEvaluacion): ?>
                            <a class="btn-resolver" href="/editar-evaluacion?curso=<?= urlencode($curso->campos['id']) ?>"><i class="fa-solid fa-list-check"></i> Editar Evaluación</a>
                        <?php else: ?>
                            <a class="btn-resolver" href="/agregar-evaluacion?curso=<?= urlencode($curso->campos['id']) ?>"><i class="fa-solid fa-plus"></i> Agregar Evaluación</a>
                        <?php endif; ?>
                        <a class="btn-resolver" href="/cantidad-inscriptos?curso=<?= urlencode($curso->campos['id']) ?>"><i class="fa-solid fa-users"></i> Inscriptos</a>
                    </div>
                </section>
            <?php endif; ?>

            <section class="sugerencias-box">
                <h3>Recomendaciones complementarias sugeridas por IA</h3>

                <?php if (!empty($recomendaciones)): ?>
                    <ul class="lista-recomendaciones">
                        <?php foreach ($recomendaciones as $rec): ?>
                            <li class="recomendacion-item">
                                <strong><?= ucfirst(htmlspecialchars($rec['tipo'])) ?>:</strong>
                                <em><?= htmlspecialchars($rec['titulo']) ?></em>
                                <?php if (!empty($rec['descripcion'])): ?>
                                    <br><small><?= htmlspecialchars($rec['descripcion']) ?></small>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No hay recomendaciones disponibles para este curso.</p>
                <?php endif; ?>
            </section>

            <section class="sugerencias-box" id="foro-consultas">
                <h3>Foro de consultas</h3>

                <?php if ($inscripto): ?>
                    <form action="/comentario/agregar" method="POST" class="form-box">
                        <input type="hidden" name="curso_id" value="<?= htmlspecialchars($curso->campos['id']) ?>">
                        <fieldset class="input-box">
                            <label for="contenido">Deja tu consulta o aporte:</label>
                            <textarea name="contenido" id="contenido" placeholder="Escribe aquí tu comentario..." required rows="3"></textarea>
                        </fieldset>
                        <button type="submit" class="button-log" style="width: auto; align-self: flex-start; padding: 0.75rem 2rem;">Enviar comentario</button>
                    </form>
                <?php else: ?>
                    <div class="foro-bloqueado-msg">
                        <p><i class="fa-solid fa-circle-info"></i> Debes inscribirte en este curso para participar en el foro de consultas.</p>
                    </div>
                <?php endif; ?>

                <div class="comentarios-lista" style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1.5rem; text-align: left;">
                    <?php if (!empty($comentarios)): ?>
                        <?php foreach ($comentarios as $com): ?>
                            <article class="recomendacion-item" style="display: flex; flex-direction: column; gap: 0.5rem; text-align: left;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <strong style="color: var(--color-accent-teal);"><?= htmlspecialchars($com['usuario_nombre']) ?></strong>
                                    <small style="color: var(--text-muted); font-size: 0.8rem;"><?= htmlspecialchars($com['fecha_creacion']) ?></small>
                                </div>
                                <p style="margin: 0; color: var(--text-secondary);"><?= nl2br(htmlspecialchars($com['contenido'])) ?></p>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted" style="font-style: italic;">No hay consultas todavía. ¡Sé el primero en comentar!</p>
                    <?php endif; ?>
                </div>
            </section>
        <?php else: ?>
            <p>No se encontró información del curso.</p>
        <?php endif; ?>
    </main>
    <?php include "parts/footer.php"; ?>
</body>