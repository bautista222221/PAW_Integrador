<?php include "parts/head.php"; ?>

<body>
    <?php include "parts/header.php"; ?>
    <main class="unidad-detalle">
        <!-- Barra de Navegación Superior (Índice y Breadcrumb) -->
        <div class="header-unidad-navegacion">
            <nav class="breadcrumb-unidad" aria-label="Breadcrumb">
                <a href="/cursos">Cursos</a>
                <span>/</span>
                <a href="/curso?id=<?= urlencode($cursoId) ?>"><?= htmlspecialchars($cursoTitulo) ?></a>
                <span>/</span>
                <span class="active">Unidad <?= htmlspecialchars($ordenActual) ?> de <?= htmlspecialchars($totalModulos) ?></span>
            </nav>

            <div class="selector-indice-unidad">
                <label for="saltar-modulo" style="font-size: 0.85rem; font-weight: 500; color: var(--text-secondary); margin-right: 0.5rem;"><i class="fa-solid fa-list-ul"></i> Unidades:</label>
                <select id="saltar-modulo" onchange="location = this.value;">
                    <?php foreach ($modulos as $mIdx => $m): ?>
                        <option value="/ver-modulo?modulo=<?= urlencode($m['id']) ?>" <?= (int)$m['id'] === (int)$modulo['id'] ? 'selected' : '' ?>>
                            <?= $mIdx + 1 ?>. <?= htmlspecialchars($m['titulo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <h2><?= htmlspecialchars($modulo["titulo"]) ?></h2>

        <?php if (!empty($modulo['descripcion'])): ?>
            <p class="unidad-descripcion"><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($modulo['descripcion'])) ?></p>
        <?php endif; ?>

        <div class="unidad-recurso-wrapper">
            <p><strong>Recurso:</strong></p>
            <?= $contenido ?>
        </div>

        <!-- Botones de Navegación Inferior -->
        <div class="navegacion-unidad-container">
            <?php if ($moduloAnterior): ?>
                <a class="btn-nav-unidad btn-anterior" href="/ver-modulo?modulo=<?= urlencode($moduloAnterior['id']) ?>"><i class="fa-solid fa-chevron-left"></i> Anterior</a>
            <?php else: ?>
                <span style="visibility: hidden;"></span> <!-- Espaciador para centrar el de volver al curso si no hay anterior -->
            <?php endif; ?>

            <a class="btn-nav-unidad btn-volver-curso" href="/curso?id=<?= urlencode($cursoId) ?>"><i class="fa-solid fa-book"></i> Volver al curso</a>

            <?php if ($moduloSiguiente): ?>
                <a class="btn-nav-unidad btn-siguiente" href="/ver-modulo?modulo=<?= urlencode($moduloSiguiente['id']) ?>">Siguiente <i class="fa-solid fa-chevron-right"></i></a>
            <?php else: ?>
                <span style="visibility: hidden;"></span> <!-- Espaciador para centrar el de volver al curso si no hay siguiente -->
            <?php endif; ?>
        </div>
    </main>
    <?php include "parts/footer.php"; ?>
</body>