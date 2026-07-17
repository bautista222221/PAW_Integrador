<?php include "parts/head.php" ?>

<body>
    <?php include "parts/header.php" ?>
    <main>
        <section class="seccion-perfil">
            <h2>USUARIO<br><small><?= htmlspecialchars($usuario["nombre"]) ?></small></h2>

            <section>
                <!-- Datos personales -->
                <div>
                    <h3>Datos Personales</h3>
                    <ul>
                        <li>
                            Nombre: <?= htmlspecialchars($usuario["nombre"]) ?>
                            <button title="Editar">✎</button>
                        </li>
                        <li>
                            Correo: <?= htmlspecialchars($usuario["correo"]) ?>
                            <button title="Editar">✎</button>
                        </li>
                        <li>
                            Contraseña: ********
                            <button title="Editar">✎</button>
                        </li>
                        <li>
                            Fecha de creación: <?= $fecha ?>
                        </li>
                        <li>
                            Rol: <?= htmlspecialchars($usuario["tipo_usuario"]) ?>
                        </li>
                    </ul>
                </div>

                <!-- Mi progreso (si lo añadís en el futuro) -->
                <div>
                    <h3>Mi progreso</h3>
                    <ul>
                        <li>Nivel: (próximamente)</li>
                        <li>Progreso: (próximamente)</li>
                    </ul>
                </div>

                <!-- Configuración -->
                <div>
                    <h3>Configuración</h3>
                    <ul>
                        <li>Notificaciones: (configurable)</li>
                    </ul>
                </div>

                <!-- Certificados -->
                <div>
                    <h3>Certificados</h3>
                    <?php if (!empty($certificados)): ?>
                        <ul>
                            <?php foreach ($certificados as $cert): ?>
                                <li style="margin-bottom: 0.75rem;">
                                    <strong><?= htmlspecialchars($cert['curso_titulo']) ?></strong>
                                    — Nota: <?= htmlspecialchars($cert['nota']) ?>/10
                                    — <?= date('d/m/Y', strtotime($cert['fecha_aprobado'])) ?>
                                    <a href="/descargar-certificado?curso=<?= urlencode($cert['curso_id']) ?>" style="color: var(--color-accent-teal); text-decoration: underline; margin-left: 0.5rem;">
                                        <i class="fa-solid fa-download"></i> Descargar
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <ul>
                            <li>No hay certificados todavía. ¡Aprueba un curso para obtener el tuyo!</li>
                        </ul>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Logout -->
            <section>
                <h3>Salir</h3>
                <a href="/logout" class="btn btn-logout">Cerrar sesión</a>
            </section>
        </section>
    </main>
    <?php include "parts/footer.php" ?>
</body>