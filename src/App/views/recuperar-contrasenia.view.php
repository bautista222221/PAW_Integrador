<!DOCTYPE html>
<html class="<?= htmlspecialchars($htmlClass ?? '') ?>" lang="es">
<?php include "parts/head.php" ?>

<body>
    <?php include "parts/header.php" ?>
    <main class="contenedor-formulario">
        <section class="container">
            <form class="form-box" action="/recuperar-contrasenia" method="POST">
                <h1 class="form-title">Recuperar contraseña</h1>
                <p class="text-muted text-center mb-4" style="font-size: 0.9rem; text-align: center;">
                    Ingresá el correo electrónico asociado a tu cuenta y te enviaremos las instrucciones para restablecer tu contraseña.
                </p>

                <fieldset class="input-box mb-3">
                    <legend>Correo Electrónico</legend>
                    <input type="email" id="inputEmail" name="inputEmail" placeholder="correo@ejemplo.com" required>
                    <i class="fa-solid fa-envelope"></i>
                </fieldset>

                <button class="button-log" type="submit">Enviar Enlace</button>
                <div style="display: flex; flex-direction: column; gap: 0.75rem; text-align: center; margin-top: 1rem;">
                    <a href="/login"><i class="fa-solid fa-arrow-left"></i> Volver al inicio de sesión</a>
                    <a href="/register">¿No tenés cuenta? Registrarme</a>
                </div>
            </form>
        </section>
    </main>
    <?php include "parts/footer.php" ?>
</body>

</html>
