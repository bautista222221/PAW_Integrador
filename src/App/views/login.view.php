<?php include 'parts/head.php' ?>

<body>
    <main class="login-main">
        <section class="container">
            <form class="form-box" action="/login" method="post">
                <h1 class="subtitulo">Iniciar Sesión</h1>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <fieldset class="input-box">
                    <label for="inputEmail">Dirección de correo electrónico</label>
                    <input id="inputEmail" type="email" name="inputEmail" placeholder="Email" required>
                    <i class="fa-solid fa-user"></i>
                </fieldset>

                <fieldset class="input-box">
                    <label for="inputPassword">Contraseña</label>
                    <input id="inputPassword" type="password" name="inputPassword" placeholder="Contraseña" required>
                    <i class="fa-solid fa-lock"></i>
                </fieldset>
                <label for="inputRecuerdame">
                    <input type="checkbox" name="recuerdame">
                    Recuérdame
                </label>
                <button class="button-log" type="submit">Acceder</button>
                <a href="/recuperar-contrasenia">¿Olvidaste tu contraseña?</a>
                <a href="/register">Registrarme</a>
            </form>
        </section>
    </main>
    <?php include "parts/footer.php" ?>
</body>

</html>