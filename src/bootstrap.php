<?php

require __DIR__ . '/../vendor/autoload.php';

use PAW\src\Core\Database\ConnectionBuilder;
use PAW\src\Core\Router;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use PAW\src\Core\Config;
use PAW\src\Core\Request;

$config = new Config;

$log = new Logger('Paw-Integrador');
$handler = new StreamHandler($config->get('LOG_PATH'));
$handler->setLevel($config->get('LOG_LEVEL'));
$log->pushHandler($handler);

$connectionBuilder = new ConnectionBuilder;
$connectionBuilder->setLogger($log);
$connection = $connectionBuilder->make($config);

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$request = new Request;

$router = new Router([$config]);
$router->setLogger($log);
$router->get("/", "ControladorPagina@index");
$router->get('/faq', 'ControladorPagina@faq');
$router->get("/cursos", "ControladorCursos@cursos");
$router->get("/curso", "ControladorCursos@curso");
$router->get("/ver-modulo", "ControladorCursos@verUnidad");
$router->get("/resolver-evaluacion", "ControladorEvaluacion@resolverEvaluacion");
$router->post("/resolver-evaluacion", "ControladorEvaluacion@procesarResolverEvaluacion");
$router->get("/agregar-curso", "ControladorCursos@agregarCurso");
$router->post("/agregar-curso", "ControladorCursos@procesarAgregarCurso");
$router->get("/editar-curso", "ControladorCursos@editarCurso");
$router->post("/editar-curso", "ControladorCursos@procesarEditarCurso");
$router->get("/agregar-unidades", "ControladorCursos@agregarUnidades");
$router->post("/agregar-unidades", "ControladorCursos@procesarAgregarUnidades");
$router->get("/agregar-evaluacion", "ControladorEvaluacion@agregarEvaluacion");
$router->post("/agregar-evaluacion", "ControladorEvaluacion@procesarAgregarEvaluacion");
$router->get("/editar-evaluacion", "ControladorEvaluacion@editarEvaluacion");
$router->post("/editar-evaluacion", "ControladorEvaluacion@procesarEditarEvaluacion");
$router->get("/resultado-evaluacion", "ControladorEvaluacion@resultadoEvaluacion");
$router->get("/descargar-certificado", "ControladorEvaluacion@descargarCertificado");
$router->get("/login", "ControladorUsuarios@login");
$router->post("/login", "ControladorUsuarios@procesarLogin");
$router->get("/register", "ControladorUsuarios@register");
$router->post("/register", "ControladorUsuarios@procesarRegistro");
$router->get("/recuperar-contrasenia", "ControladorUsuarios@recuperarContrasenia");
$router->post("/recuperar-contrasenia", "ControladorUsuarios@procesarRecuperarContrasenia");
$router->get("/user-profile", "ControladorUsuarios@userProfile");
$router->get("/logout", "ControladorUsuarios@logout");
$router->post("/modelo-ia", "ControladorCursos@modeloIA");
$router->get('/cantidad-inscriptos', 'ControladorInscripcion@cantidadInscriptos');
$router->get('/listar-inscriptos', 'ControladorInscripcion@listarInscriptos');
$router->post('/inscribirse', 'ControladorInscripcion@inscribir');
$router->post('/comentario/agregar', 'ControladorCursos@agregarComentario');
$router->get('/soporte', 'ControladorPagina@soporte');
$router->post('/soporte', 'ControladorPagina@procesarSoporte');