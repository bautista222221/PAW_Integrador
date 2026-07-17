<?php include "parts/head.php" ?>

<body>
    <main class="register-main">
        <section class="container">
            <form class="form-box" action="/register" method="post">
                <h1 class="subtitulo">Registrarse</h1>

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
                    <label for="inputNombre">Nombre Completo</label>
                    <input id="inputNombre" type="text" name="inputNombre" placeholder="Nombre" required>
                    <i class="fa-solid fa-user"></i>
                </fieldset>
                <fieldset class="input-box">
                    <label for="inputEmail">Dirección de correo electrónico</label>
                    <input id="inputEmail" type="email" name="inputEmail" placeholder="Email" required>
                    <i class="fa-solid fa-envelope"></i>
                </fieldset>
                <fieldset class="input-box">
                    <label for="inputPassword">Contraseña</label>
                    <input id="inputPassword" type="password" name="inputPassword" placeholder="Contraseña" required>
                    <i class="fa-solid fa-lock"></i>
                </fieldset>
                <fieldset class="input-box">
                    <label for="inputConfirmarPassword">Confirmar Contraseña</label>
                    <input id="inputConfirmarPassword" type="password" name="inputConfirmarPassword"
                        placeholder="Confirmar Contraseña" required>
                    <i class="fa-solid fa-lock"></i>
                </fieldset>
                <p>Al registrarse, aceptas nuestros términos y condiciones</p>
                <button class="button-reg" type="submit">Registrarse</button>
                <p>¿Ya tienes cuenta creada? <strong><a class="login-link" href="/login">Iniciar Sesión</a></strong></a>
                </p>
            </form>
        </section>
    </main>
    <?php include "parts/footer.php" ?>
</body>