<?php include "parts/head.php" ?>

<body>
    <?php include "parts/header.php" ?>

    <main class="soporte-main-container">
        <!-- Hero Section/Banner -->
        <section class="soporte-hero">
            <div class="hero-glow"></div>
            <div class="hero-content">
                <h2>¿Cómo podemos ayudarte hoy?</h2>
                <p>Si tienes dudas sobre el funcionamiento de la plataforma, problemas de acceso o consultas sobre los cursos, nuestro equipo de soporte técnico está a tu disposición.</p>
            </div>
        </section>

        <!-- Notification Alert for Form Submission success -->
        <?php if ($enviado): ?>
            <div class="alert-success-soporte animate-fade-in">
                <div class="alert-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="alert-content">
                    <h4>¡Mensaje enviado con éxito!</h4>
                    <p>Hemos recibido tu consulta de soporte técnico. Nuestro equipo se pondrá en contacto contigo a la brevedad en tu dirección de correo electrónico.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Split Grid Content -->
        <div class="soporte-layout">
            <!-- Left Column: Contact Cards -->
            <section class="soporte-info-column">
                <h3>Información de Contacto</h3>
                
                <div class="soporte-card-info">
                    <div class="card-icon-wrapper">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <div class="card-text">
                        <h4>Correo Electrónico</h4>
                        <p><a href="mailto:padConsultas@gmail.com">padConsultas@gmail.com</a></p>
                        <span>Respuesta garantizada en menos de 24 horas.</span>
                    </div>
                </div>

                <div class="soporte-card-info">
                    <div class="card-icon-wrapper">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <div class="card-text">
                        <h4>Atención Telefónica</h4>
                        <p><a href="tel:2346500617">2346-500617</a></p>
                        <span>Lunes a Viernes de 09:00 a 18:00 hs.</span>
                    </div>
                </div>

                <div class="soporte-card-info">
                    <div class="card-icon-wrapper">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div class="card-text">
                        <h4>Horario de Operaciones</h4>
                        <p>Plataforma Online 24/7</p>
                        <span>Mantenimiento los domingos a las 02:00 am.</span>
                    </div>
                </div>
            </section>

            <!-- Right Column: Support Form -->
            <section class="soporte-form-column">
                <h3>Envíanos un mensaje</h3>
                <div class="soporte-form-box">
                    <form action="/soporte" method="POST" class="soporte-form">
                        <fieldset class="form-group-soporte">
                            <label for="nombre">Nombre Completo</label>
                            <div class="input-wrapper-soporte">
                                <i class="fa-solid fa-user"></i>
                                <input type="text" id="nombre" name="nombre" placeholder="Ej: Juan Pérez" required>
                            </div>
                        </fieldset>

                        <fieldset class="form-group-soporte">
                            <label for="email">Correo Electrónico</label>
                            <div class="input-wrapper-soporte">
                                <i class="fa-solid fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Ej: juan.perez@email.com" required>
                            </div>
                        </fieldset>

                        <fieldset class="form-group-soporte">
                            <label for="asunto">Asunto</label>
                            <div class="input-wrapper-soporte">
                                <i class="fa-solid fa-circle-info"></i>
                                <input type="text" id="asunto" name="asunto" placeholder="Ej: Error al resolver cuestionario" required>
                            </div>
                        </fieldset>

                        <fieldset class="form-group-soporte">
                            <label for="mensaje">Detalle de tu Consulta</label>
                            <div class="textarea-wrapper-soporte">
                                <textarea id="mensaje" name="mensaje" placeholder="Escribe aquí tu consulta de la forma más detallada posible..." rows="5" required></textarea>
                            </div>
                        </fieldset>

                        <button type="submit" class="btn-submit-soporte">
                            <i class="fa-solid fa-paper-plane"></i> Enviar consulta
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </main>

    <?php include "parts/footer.php" ?>
</body>
