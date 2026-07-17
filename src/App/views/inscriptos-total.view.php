<?php include "parts/head.php" ?>
<body>
    <?php include "parts/header.php" ?>
    
    <main class="dashboard-stats-container">
        <section class="dashboard-stats-card">
            <div class="stat-icon-wrapper">
                <i class="fa-solid fa-users"></i>
            </div>
            
            <h2>Total de Inscriptos</h2>
            <div class="stat-number"><?= htmlspecialchars($total) ?></div>
            <p class="stat-desc">Estudiantes registrados y cursando activamente en este momento.</p>
            
            <div class="stat-actions">
                <a class="btn-primary-action" href="/listar-inscriptos?id=<?= urlencode($_GET['curso']) ?>">
                    <i class="fa-solid fa-list-check"></i> Ver detalle de alumnos
                </a>
                <a class="btn-secondary-action" href="/curso?id=<?= urlencode($_GET['curso']) ?>">
                    <i class="fa-solid fa-arrow-left"></i> Volver al curso
                </a>
            </div>
        </section>
    </main>

    <?php include "parts/footer.php" ?>
</body>
