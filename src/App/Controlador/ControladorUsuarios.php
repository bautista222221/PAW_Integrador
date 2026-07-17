<?php

namespace PAW\src\App\Controlador;

use PAW\src\Core\Controlador;
use PAW\src\App\Modelos\ColeccionUsuarios;

class ControladorUsuarios extends Controlador
{
    public ?string $modelo = ColeccionUsuarios::class;

    public function login()
    {
        global $request;
        $datos = $_SESSION["usuario"] ?? null;
        if (!is_null($datos)) {
            $this->userProfile();
            return;
        }
        $htmlClass = "mi-cuenta-pages";
        $titulo = "PAD - Login";
        require $this->viewsDir . 'login.view.php';
    }

    public function userProfile()
    {
        if (!isset($_SESSION['usuario'])) {
            echo "<script>alert('⚠️ Debes iniciar sesión para acceder a tu perfil'); window.location.href = '/login';</script>";
            return;
        }
        $usuario = $_SESSION['usuario'];
        $fecha = date("d/m/Y", strtotime($usuario["fecha_creacion"]));
        $titulo = 'PAD - Mi cuenta';
        $htmlClass = "mi-cuenta-pages";

        // Cargar certificados aprobados
        $sql = "SELECT i.nota, i.fecha_aprobado, c.titulo as curso_titulo, c.id as curso_id
                FROM inscripciones i
                JOIN cursos c ON i.curso_id = c.id
                WHERE i.usuario_id = :usuario_id AND i.aprobado = true
                ORDER BY i.fecha_aprobado DESC";
        global $connection;
        $qb = new \PAW\src\Core\Database\QueryBuilder($connection);
        $certificados = $qb->selectRaw($sql, ['usuario_id' => $usuario['id']]);

        require $this->viewsDir . 'user-profile.view.php';
    }

    public function logout()
    {
        session_unset();           // Limpiamos todas las variables de sesión
        session_destroy();         // Destruimos la sesión
        echo "<script>
            alert('✅ Sesión cerrada exitosamente');
            window.location.href = '/';
        </script>";
    }

    public function procesarLogin()
    {
        global $request;
        // Recoger los datos del formulario
        $email = $request->get('inputEmail');
        $password = $request->get('inputPassword');

        // Validación mínima de formato
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = '⚠️ El correo electrónico ingresado no es válido.';
            header("Location: /login");
            return;
        }
        if (empty($password)) {
            $_SESSION['error'] = '⚠️ La contraseña no puede estar vacía.';
            header("Location: /login");
            return;
        }
        if (strlen($password) < 8) {
            $_SESSION['error'] = '⚠️ La contraseña debe tener al menos 8 caracteres.';
            header("Location: /login");
            return;
        }

        $usuario = $this->modeloInstancia->autenticar($email, $password);
        if (empty($usuario)) {
            $_SESSION['error'] = '⚠️ Correo electrónico o contraseña incorrectos.';
            header("Location: /login");
            return;
        }
        $_SESSION['usuario'] = $usuario->campos;
        $_SESSION['success'] = '✅ ¡Sesión iniciada exitosamente!';
        header("Location: /");
        exit();
    }

    public function register()
    {
        $titulo = "PAD - Registro";
        $htmlClass = "mi-cuenta-pages";
        require $this->viewsDir . 'register.view.php';
    }

    public function procesarRegistro()
    {
        global $request;
        if (
            empty($request->get('inputNombre')) ||
            empty($request->get('inputEmail')) ||
            empty($request->get('inputPassword')) ||
            empty($request->get('inputConfirmarPassword'))
        ) {
            $_SESSION['error'] = '⚠️ Todos los campos obligatorios deben completarse.';
            header("Location: /register");
            return;
        }
        // Recoger los datos del formulario
        $nombre = $request->get('inputNombre');
        $email = $request->get('inputEmail');
        $password = $request->get('inputPassword');
        $confirmarPassword = $request->get('inputConfirmarPassword');
        $datos = [
            'nombre' => $nombre,
            'correo' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = '⚠️ El correo electrónico ingresado no es válido.';
            header("Location: /register");
            return;
        }

        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre)) {
            $_SESSION['error'] = '⚠️ El nombre ingresado no es válido.';
            header("Location: /register");
            return;
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = '⚠️ La contraseña debe tener al menos 8 caracteres.';
            header("Location: /register");
            return;
        }

        if ($password !== $confirmarPassword) {
            $_SESSION['error'] = '⚠️ Las contraseñas no coinciden.';
            header("Location: /register");
            return;
        }

        if ($this->modeloInstancia->existeEmail($email)) {
            $_SESSION['error'] = '⚠️ El correo electrónico ya está registrado.';
            header("Location: /register");
            return;
        }

        // Crear un nuevo usuario
        if (!$this->modeloInstancia->crear($datos)) {
            $_SESSION['error'] = '⚠️ Ocurrió un error al registrar el usuario.';
            header("Location: /register");
            return;
        }
        // Auto-login del usuario recién registrado
        $usuarioCreado = $this->modeloInstancia->queryBuilder->select('usuarios', ['correo' => $email]);
        if (!empty($usuarioCreado)) {
            $_SESSION['usuario'] = $usuarioCreado[0];
        }
        
        $_SESSION['success'] = '✅ ¡Registro exitoso!';
        header("Location: /user-profile");
        exit();
    }

    public function editarUsuario()
    {
        $datos = $_SESSION['usuario'];
        $titulo = 'PAWPrints - Editar usuario';
        $htmlClass = "mi-cuenta-pages";
        require $this->viewsDir . 'editar-usuario.view.php';
    }

    public function procesarEditarUsuario()
    {
        global $request;
        if (
            empty($request->get('password')) ||
            empty($request->get('confirmar_password'))
        ) {
            echo "<script>alert('⚠️ Todos los campos obligatorios deben completarse'); window.history.back();</script>";
            return;
        }

        $nombre = $request->get('inputNombre');
        if (empty($nombre)) {
            $nombre = $_SESSION['usuario']['nombre'];
        }
        $email = $request->get('inputEmail');
        if (empty($email)) {
            $email = $_SESSION['usuario']['email'];
        }
        $password = $request->get('password');
        $confirmarPassword = $request->get('confirmar_password');
        $datos = [
            'nombre' => $nombre,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('⚠️ Email no valido'); window.history.back();</script>";
            return;
        }

        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre)) {
            echo "<script>alert('⚠️ Nombre no valido'); window.history.back();</script>";
            return;
        }

        if (strlen($password) < 8) {
            echo "<script>alert('⚠️ La contraseña debe tener al menos 8 caracteres'); window.history.back();</script>";
            return;
        }

        if ($password !== $confirmarPassword) {
            echo "<script>alert('⚠️ Las contraseña no coinciden'); window.history.back();</script>";
            return;
        }

        // Actualizar el usuario
        if (!$this->modeloInstancia->actualizar($_SESSION['usuario']['id'], $datos)) {
            echo "<script>alert('⚠️ Error al actualizar el usuario'); window.history.back();</script>";
            return;
        }

        $_SESSION['usuario'] = array_merge($_SESSION['usuario'], $datos);
        // Éxito: redirigir a página principal u otra
        echo "<script>
            alert('✅ Usuario actualizado exitosamente');
            window.location.href = '/mi-cuenta';
        </script>";
    }

    public function recuperarContraseña()
    {
        $titulo = 'PAWPrints - Recuperar contraseña';
        $htmlClass = "mi-cuenta-pages";
        require $this->viewsDir . 'recuperar-contraseña.view.php';
    }

    public function procesarRecuperarContraseña()
    {
        // Recoger los datos del formulario
        $email = $_POST['inputEmail'];
        $archivo = __DIR__ . "/../../login.txt";

        $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $emailEncontrado = false;

        foreach ($lineas as $linea) {
            list($id, $emailArchivo, $passArchivo, $nombre, $apellido) = explode('|', trim($linea));
            if ($email === $emailArchivo) {
                $emailEncontrado = true;
                break;
            }
        }

        if ($emailEncontrado) {
            echo "✅ Se ha enviado un enlace para restablecer tu contraseña a tu correo electrónico.";
        } else {
            echo "❌ El email no está registrado.";
        }
    }
}