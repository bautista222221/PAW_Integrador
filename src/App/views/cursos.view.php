<?php include "parts/head.php" ?>

<body>
    <?php include "parts/header.php" ?>
    
    <main class="cursos-main-container">
        <!-- Hero Section for Courses Page -->
        <section class="cursos-hero">
            <div class="hero-glow"></div>
            <div class="hero-content">
                <h2>Plataforma de Aprendizaje Dinámico</h2>
                <p>Explora nuestra oferta académica de cursos prácticos diseñados para potenciar tus habilidades de programación con feedback inmediato.</p>
            </div>
        </section>

        <div class="cursos-layout">
            <!-- Left Side: Courses Content -->
            <div class="cursos-content-area">
                <section class="temas" id="cursos-disponibles">
                    <h3>Cursos Disponibles</h3>
                    <section class="temas-box">
                        <?php foreach ($cursos as $curso): ?>
                            <a href="/curso?id=<?= urlencode($curso->campos['id']) ?>" class="curso-card">
                                <div class="curso-card-header">
                                    <span class="badge-curso"><i class="fa-solid fa-graduation-cap"></i> Curso</span>
                                </div>
                                <h4><?= htmlspecialchars($curso->campos['titulo']) ?></h4>
                                <p><?= nl2br(htmlspecialchars($curso->campos['descripcion'])) ?></p>
                                <span class="btn-ver-mas">Empezar a aprender <i class="fa-solid fa-arrow-right"></i></span>
                            </a>
                        <?php endforeach; ?>
                    </section>
                </section>

                <section class="seccion-cursos-activos">
                    <h3>Cursos Activos</h3>
                    <?php if (!isset($_SESSION['usuario'])): ?>
                        <div class="empty-state-card">
                            <div class="empty-icon-wrapper">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <h4>Inicia sesión para ver tus cursos</h4>
                            <p>Debes acceder a tu cuenta para visualizar los cursos en los que te has inscrito.</p>
                            <a href="/login" class="btn-explore">Acceder</a>
                        </div>
                    <?php elseif (empty($cursosActivos)): ?>
                        <div class="empty-state-card">
                            <div class="empty-icon-wrapper">
                                <i class="fa-solid fa-book-open-reader"></i>
                            </div>
                            <h4>No tienes ningún curso activo</h4>
                            <p>Inscríbete en alguno de los cursos disponibles para iniciar tu ruta de aprendizaje hoy mismo.</p>
                            <a href="#cursos-disponibles" class="btn-explore">Explorar Cursos</a>
                        </div>
                    <?php else: ?>
                        <section class="temas-box">
                            <?php foreach ($cursosActivos as $cursoActivo): ?>
                                <a href="/curso?id=<?= urlencode($cursoActivo->campos['id']) ?>" class="curso-card active-course-card">
                                    <div class="curso-card-header">
                                        <span class="badge-curso active-badge"><i class="fa-solid fa-circle-play"></i> En curso</span>
                                    </div>
                                    <h4><?= htmlspecialchars($cursoActivo->campos['titulo']) ?></h4>
                                    <p><?= nl2br(htmlspecialchars($cursoActivo->campos['descripcion'])) ?></p>
                                    <span class="btn-ver-mas">Continuar <i class="fa-solid fa-arrow-right"></i></span>
                                </a>
                            <?php endforeach; ?>
                        </section>
                    <?php endif; ?>
                </section>
            
                <?php if ($permiso): ?>
                    <section class="admin-cursos-actions">
                        <h3>Acciones de Administración</h3>
                        <div class="temas-box">
                            <a href="/agregar-curso" class="curso-card action-card">
                                <i class="fa-solid fa-circle-plus action-icon"></i>
                                <h4>Agregar un nuevo curso</h4>
                                <p>Crea y configura un nuevo curso interactivo en la plataforma.</p>
                            </a>
                        </div>
                    </section>
                <?php endif; ?>
            </div>

            <!-- Right Side: Sidebar Informative -->
            <aside class="cursos-sidebar">
                <div class="sidebar-card promo-card">
                    <div class="promo-image-container">
                        <img src="/images/portadaCurso.jpg" alt="Aprender Programación en PAD">
                        <div class="promo-overlay"></div>
                    </div>
                    <div class="promo-content">
                        <h4>Metodología Práctica</h4>
                        <p>Nuestra plataforma te guía paso a paso con cuestionarios de opción múltiple, ordenación y completado de texto con corrección inmediata en cada unidad.</p>
                    </div>
                </div>

                <div class="sidebar-card highlights-card">
                    <h4>¿Por qué estudiar en PAD?</h4>
                    <ul class="highlights-list">
                        <li>
                            <span class="highlight-icon">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </span>
                            <div class="highlight-text">
                                <strong>Flexibilidad Total</strong>
                                <p>Estudia a tu propio ritmo, desde cualquier dispositivo y en el momento que quieras.</p>
                            </div>
                        </li>
                        <li>
                            <span class="highlight-icon">
                                <i class="fa-solid fa-circle-nodes"></i>
                            </span>
                            <div class="highlight-text">
                                <strong>Estructura por Módulos</strong>
                                <p>Contenido ordenado secuencialmente para garantizar una curva de aprendizaje amigable.</p>
                            </div>
                        </li>
                        <li>
                            <span class="highlight-icon">
                                <i class="fa-solid fa-clipboard-check"></i>
                            </span>
                            <div class="highlight-text">
                                <strong>Autoevaluaciones</strong>
                                <p>Mide tus progresos de manera directa y obtén retroalimentación inmediata.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </main>

    <?php include "parts/footer.php" ?>
</body>