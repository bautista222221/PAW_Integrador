<?php include "parts/head.php" ?>
<body>
    <?php include "parts/header.php" ?>
    
    <main class="inscriptos-list-main">
        <header class="inscriptos-header">
            <div class="header-title-area">
                <h2>Listado de Inscriptos</h2>
                <?php if (!empty($inscriptos)): ?>
                    <span class="badge-count"><i class="fa-solid fa-user-graduate"></i> <?= count($inscriptos) ?> estudiantes</span>
                <?php endif; ?>
            </div>
            <a class="btn-back" href="/curso?id=<?= urlencode($idCurso) ?>">
                <i class="fa-solid fa-arrow-left"></i> Volver al curso
            </a>
        </header>

        <?php if (empty($inscriptos)): ?>
            <div class="empty-state-card">
                <div class="empty-icon-wrapper">
                    <i class="fa-solid fa-users-slash"></i>
                </div>
                <h4>Sin alumnos inscriptos</h4>
                <p>Nadie se ha registrado en este curso todavía. ¡Invita a nuevos estudiantes a participar!</p>
                <a href="/curso?id=<?= urlencode($idCurso) ?>" class="btn-explore">Volver al curso</a>
            </div>
        <?php else: ?>
            <div class="inscriptos-table-container">
                <table class="inscriptos-table">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>ID Usuario</th>
                            <th>Fecha de Inscripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inscriptos as $ins): 
                            // Obtener las iniciales del nombre
                            $nombre = htmlspecialchars($ins['nombre']);
                            $inicial = strtoupper(substr($nombre, 0, 1));
                        ?>
                            <tr>
                                <td class="student-cell">
                                    <div class="student-avatar"><?= $inicial ?></div>
                                    <div class="student-info">
                                        <span class="student-name"><?= $nombre ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="student-id"><i class="fa-solid fa-hashtag"></i> <?= htmlspecialchars($ins['usuario_id']) ?></span>
                                </td>
                                <td>
                                    <span class="enroll-date"><i class="fa-regular fa-calendar-days"></i> <?= htmlspecialchars($ins['fecha_inscripcion']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <?php include "parts/footer.php" ?>
</body>
