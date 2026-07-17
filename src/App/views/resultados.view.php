<?php include 'parts/head.php' ?>

<body>
    <?php include 'parts/header.php' ?>

    <main class="curso-detalle">
        <section class="curso-box">
             <?php if ($resultado): ?>
                <h2>Resultado de la Evaluación</h2>
                <p><strong>Evaluación:</strong> <?= htmlspecialchars($resultado['evaluacion_titulo'] ?? $resultado['curso_id']) ?></p>
                <p><strong>Respuestas correctas:</strong> <?= $resultado['correctas'] ?> de <?= $resultado['total'] ?></p>
                <p><strong>Puntuación:</strong> <?= $resultado['puntuacion'] ?>/10</p>

                <?php if ($resultado['puntuacion'] > 6): ?>
                    <p>🎉 ¡Felicitaciones! Has aprobado el curso y puedes obtener tu certificado.</p>
                    <div class="resultados-actions">
                        <a class="btn-resolver btn-certificado" href="/descargar-certificado?curso=<?= urlencode($resultado['curso_id']) ?>"><i class="fa-solid fa-award"></i> Descargar Certificado</a>
                        <a class="btn-resolver btn-secundario" href="/cursos"><i class="fa-solid fa-book-open"></i> Volver a Cursos</a>
                    </div>
                <?php else: ?>
                    <p>😕 No alcanzaste la puntuación necesaria para aprobar.</p>
                    <div class="resultados-actions">
                        <form action="/resolver-evaluacion" method="get" style="display: inline-block; margin: 0;">
                            <input type="hidden" name="curso" value="<?= htmlspecialchars($resultado['curso_id']) ?>">
                            <button class="btn-resolver btn-reintentar" type="submit">Volver a intentar el examen</button>
                        </form>
                        <a class="btn-resolver btn-secundario" href="/cursos"><i class="fa-solid fa-book-open"></i> Volver a Cursos</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p>No hay resultados disponibles. Por favor, realiza una evaluación.</p>
            <?php endif; ?>
        </section>
    </main>

    <?php include 'parts/footer.php'; ?>
</body>