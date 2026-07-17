<?php include "parts/head.php"; ?>

<body>
    <?php include 'parts/header.php'; ?>

    <main class="faqs-main-container">
        <!-- Hero Header -->
        <section class="faqs-hero">
            <div class="hero-glow"></div>
            <div class="hero-content">
                <h2>Preguntas Frecuentes</h2>
                <p>Encuentra respuestas rápidas a las dudas más comunes sobre la Plataforma de Aprendizaje Dinámico y sus cursos.</p>
            </div>
        </section>

        <!-- Category Section -->
        <div class="faqs-layout">
            <div class="faqs-content">
                <!-- Categoria 1: Cuenta y Acceso -->
                <div class="faq-category">
                    <h3><i class="fa-solid fa-user-lock"></i> Cuenta y Acceso</h3>
                    
                    <details class="faq-item">
                        <summary class="faq-pregunta">
                            <span>¿Cómo puedo ingresar en el sitio?</span>
                            <i class="fa-solid fa-chevron-down faq-arrow"></i>
                        </summary>
                        <div class="faq-respuesta">
                            <p>Para registrarte o iniciar sesión, haz clic en el botón <strong>"Acceder"</strong> situado en la esquina superior derecha del menú. Si ya has iniciado sesión, tu perfil estará disponible bajo la opción "Mi perfil".</p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary class="faq-pregunta">
                            <span>¿Olvidé mi contraseña, qué hago?</span>
                            <i class="fa-solid fa-chevron-down faq-arrow"></i>
                        </summary>
                        <div class="faq-respuesta">
                            <p>Puedes restablecer tu contraseña haciendo clic en el enlace <strong>"¿Olvidaste tu contraseña?"</strong> en la pantalla de inicio de sesión. Te enviaremos instrucciones de recuperación a tu correo electrónico registrado.</p>
                        </div>
                    </details>
                </div>

                <!-- Categoria 2: Cursos y Aprendizaje -->
                <div class="faq-category">
                    <h3><i class="fa-solid fa-laptop-code"></i> Cursos y Aprendizaje</h3>
                    
                    <details class="faq-item">
                        <summary class="faq-pregunta">
                            <span>¿Cómo me inscribo a un curso?</span>
                            <i class="fa-solid fa-chevron-down faq-arrow"></i>
                        </summary>
                        <div class="faq-respuesta">
                            <p>Explora la lista completa en la sección <strong>"Cursos"</strong>. Haz clic en el curso que te interese para ver su descripción, temario y requisitos, y presiona el botón <strong>"Inscribirse"</strong> para iniciar tu aprendizaje de inmediato.</p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary class="faq-pregunta">
                            <span>¿Cómo funciona la corrección automática?</span>
                            <i class="fa-solid fa-chevron-down faq-arrow"></i>
                        </summary>
                        <div class="faq-respuesta">
                            <p>Cada módulo cuenta con cuestionarios interactivos de opción múltiple, ordenación y completado de texto. Al enviarlos, nuestro motor procesará las respuestas en tiempo real, dándote retroalimentación inmediata sobre tus aciertos y errores.</p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary class="faq-pregunta">
                            <span>¿Puedo repetir las autoevaluaciones?</span>
                            <i class="fa-solid fa-chevron-down faq-arrow"></i>
                        </summary>
                        <div class="faq-respuesta">
                            <p>Sí. En PAD creemos en el aprendizaje continuo. Puedes volver a leer las unidades de estudio y realizar las autoevaluaciones tantas veces como sea necesario para consolidar tu conocimiento.</p>
                        </div>
                    </details>
                </div>

                <!-- Categoria 3: Soporte y Contacto -->
                <div class="faq-category">
                    <h3><i class="fa-solid fa-headset"></i> Soporte y Contacto</h3>
                    
                    <details class="faq-item">
                        <summary class="faq-pregunta">
                            <span>¿Cómo contacto al soporte técnico?</span>
                            <i class="fa-solid fa-chevron-down faq-arrow"></i>
                        </summary>
                        <div class="faq-respuesta">
                            <p>Puedes ingresar a nuestra sección de <strong>Soporte Técnico</strong> en el menú del pie de página y rellenar nuestro formulario de consultas. También puedes escribirnos de forma directa al correo <a href="mailto:padConsultas@gmail.com">padConsultas@gmail.com</a>.</p>
                        </div>
                    </details>
                </div>
            </div>
            
            <!-- Side Panel CTA -->
            <aside class="faq-sidebar">
                <div class="faq-cta-card">
                    <i class="fa-solid fa-circle-info cta-icon"></i>
                    <h4>¿No encuentras lo que buscas?</h4>
                    <p>Si tu consulta no está listada aquí, no dudes en escribirle a nuestro equipo técnico.</p>
                    <a href="/soporte" class="btn-cta-soporte"><i class="fa-solid fa-headset"></i> Ir a Soporte</a>
                </div>
            </aside>
        </div>
    </main>

    <?php include 'parts/footer.php'; ?>
</body>

</html>