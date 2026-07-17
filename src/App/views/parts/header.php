<header>
    <h1>
        <a class="logo" href="/">
            <img src="images/PAD.png" alt="PAD Logo">
        </a>
    </h1>
    <nav class="nav-bar">
        <ul>
            <li>
                <a href="/cursos" class="nav-link">Cursos</a>
            </li>
            <?php if (isset($_SESSION['usuario'])): ?>
                <li>
                    <a href="/user-profile" class="nav-link profile-link"><i class="fa-solid fa-circle-user"></i> Mi perfil</a>
                </li>
                <li>
                    <a href="/logout" class="nav-btn nav-btn-outline">Cerrar sesión</a>
                </li>
            <?php else: ?>
                <li>
                    <a href="/login" class="nav-btn nav-btn-solid">Acceder</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <button class="hamburger-icon"><i class="fa-solid fa-bars"></i></button>
</header>