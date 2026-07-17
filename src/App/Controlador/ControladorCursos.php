<?php

namespace PAW\src\App\Controlador;
use PAW\src\Core\Controlador;
use PAW\src\App\Modelos\ColeccionCursos;
use PAW\src\Core\Services\CloudinaryService;

class ControladorCursos extends Controlador
{
    public ?string $modelo = ColeccionCursos::class;

    public function cursos()
    {
        $titulo = "PAD - Cursos";
        $cursos = $this->modeloInstancia->getAll();
        $permiso = $this->validarAdmin();

        $cursosActivos = [];
        if (isset($_SESSION['usuario'])) {
            $usuarioId = $_SESSION['usuario']['id'];
            $cursosActivos = $this->modeloInstancia->getCursosActivosUsuario($usuarioId);
        }

        require $this->viewsDir . 'cursos.view.php';
    }

    public function validarSesion()
    {
        if (!isset($_SESSION['usuario'])) {
            $this->redireccionar("/login");
            exit;
        }
    }

    public function validarAdmin(): bool
    {
        if (!isset($_SESSION['usuario'])) {
            return false;
        }

        $tipo_usuario = $_SESSION['usuario']['tipo_usuario'] ?? null;

        return $tipo_usuario === 'admin';
    }


    public function curso()
    {
        $this->validarSesion();
        global $request;
        $cursoId = $request->get('id');
        $usuarioId = $_SESSION["usuario"]["id"];
        
        // Cargar todo en la menor cantidad de queries posible (4 en vez de 7+)
        $data = $this->modeloInstancia->getCursoCompleto((int)$cursoId, (int)$usuarioId);
        
        $cursoData = $data['curso'];
        if (!$cursoData) {
            $this->redireccionar("/cursos");
            exit;
        }
        
        // Construir objeto Curso a partir de los datos ya cargados
        $curso = new \PAW\src\App\Modelos\Curso();
        $curso->setQueryBuilder($this->modeloInstancia->queryBuilder);
        $curso->set($cursoData);
        
        $temas = $data['temas'];
        $modulos = $data['modulos'];
        $recomendaciones = $curso->campos['recomendaciones'] ?? [];
        $inscripto = (bool)$cursoData['inscripto'];
        $tieneEvaluacion = (bool)$cursoData['tiene_evaluacion'];
        $comentarios = $data['comentarios'];
        $permiso = $this->validarAdmin();
        
        // Crear progresos solo para módulos que no los tienen (progreso_id = null)
        foreach ($modulos as &$modulo) {
            if (empty($modulo['progreso_id'])) {
                $this->modeloInstancia->crearProgreso($usuarioId, $cursoId, $modulo['id']);
                $modulo["completado"] = false;
            } else {
                $modulo["completado"] = (bool) $modulo["completado"];
            }
        }
        unset($modulo);
        
        $evaluacionData = $tieneEvaluacion ? [true] : [];
        $titulo = htmlspecialchars($curso->campos['titulo'] ?? 'Curso no encontrado');
        require $this->viewsDir . 'curso.view.php';
    }

    public function verUnidad()
    {
        $this->validarSesion();
        global $request;
        $moduloId = $request->get("modulo");
        $modulo = $this->modeloInstancia->getModulo($moduloId);
        $cursoId = $modulo["curso_id"];
        $usuarioId = $_SESSION["usuario"]["id"];

        // Validar inscripción del usuario en este curso
        $inscripto = $this->modeloInstancia->existeInscripcion($usuarioId, $cursoId);
        if (!$inscripto) {
            $this->redireccionar("/curso?id=" . urlencode($cursoId));
            exit;
        }

        $contenido = $this->embedRecurso($modulo["tipo"], $modulo["url"]);
        $this->modeloInstancia->marcarCompletado($moduloId, $cursoId, $usuarioId);
        
        // Obtener y ordenar todos los módulos del curso por su campo orden
        $modulos = $this->modeloInstancia->getModulosCurso($cursoId);
        usort($modulos, function($a, $b) {
            return (int)($a['orden'] ?? 0) <=> (int)($b['orden'] ?? 0);
        });

        // Determinar índices de navegación
        $indiceActual = -1;
        foreach ($modulos as $key => $m) {
            if ((int)$m['id'] === (int)$moduloId) {
                $indiceActual = $key;
                break;
            }
        }

        $moduloAnterior = ($indiceActual > 0) ? $modulos[$indiceActual - 1] : null;
        $moduloSiguiente = ($indiceActual < count($modulos) - 1) ? $modulos[$indiceActual + 1] : null;
        $ordenActual = $indiceActual + 1;
        $totalModulos = count($modulos);

        // Obtener el título del curso para las migas de pan
        $curso = $this->modeloInstancia->get($cursoId);
        $cursoTitulo = $curso->campos['titulo'] ?? 'Curso';

        $titulo = "PAD - " . ($modulo["titulo"] ?? "Unidad");
        require $this->viewsDir . 'ver-unidad.view.php';
    }

    public function agregarCurso()
    {
        if (!$this->validarAdmin()) {
            $this->redireccionar("/login");
            exit;
        }
        $titulo = "PAD - Agregar Curso";
        require $this->viewsDir . 'agregar-curso.view.php';
    }
    public function procesarAgregarCurso()
    {
        if (!$this->validarAdmin()) {
            $this->redireccionar("/login");
            exit;
        }
        global $request;
        $tituloCurso = $request->get("titulo");
        $descripcionCurso = $request->get("descripcion");
        $recomendaciones = $request->get("recomendaciones_json") ?? null;
        $creado_por = $_SESSION["usuario"]["id"];
        $nivel = $request->get("nivel");
        $duracion = (int) $request->get("duracion");

        // Imagen del curso
        $rutaImagenCurso = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $rutaImagenCurso = $this->procesarArchivoSubido($_FILES['imagen']);
            } else {
                $this->logger->error("Error PHP al subir imagen de portada: " . $_FILES['imagen']['error']);
                echo "<script>alert('⚠️ Error al subir la imagen del curso (Código: " . $_FILES['imagen']['error'] . "). Comprueba el tamaño del archivo.'); window.history.back();</script>";
                return;
            }
        }

        $datosCurso = [
            'titulo' => $tituloCurso,
            'descripcion' => $descripcionCurso,
            'recomendaciones' => $recomendaciones,
            'creado_por' => $creado_por,
            'nivel' => $nivel,
            'duracion' => $duracion,
            'imagen' => $rutaImagenCurso
        ];

        $cursoId = $this->modeloInstancia->crear($datosCurso);

        if (!$cursoId) {
            echo "<script>alert('⚠️ Error al crear el curso'); window.history.back();</script>";
            return;
        }
        $temas = $request->get("temario");
        foreach ($temas as $orden => $temaTitulo) {
            $temaDatos = [
                'curso_id' => $cursoId,
                'titulo' => $temaTitulo,
            ];

            if (!$this->modeloInstancia->guardarTema($temaDatos)) {
                echo "<script>alert('⚠️ Error al guardar un tema'); window.history.back();</script>";
                return;
            }
        }

        $modulos = $request->get("modulos");
        $archivosModulo = $_FILES['modulos'] ?? [];

        $i = 0;
        foreach ($modulos as $indice => $modulo) {
            $contenidoTipo = null;
            $contenidoUrl = null;

            // Verificar si es un link
            if (!empty($modulo['link'])) {
                $contenidoTipo = 'link';
                $contenidoUrl = $modulo['link'];
            }
            // Si no es link, verificar si se subió un archivo
            elseif (
                !empty($archivosModulo['name'][$indice]['archivo']) &&
                $archivosModulo['error'][$indice]['archivo'] === 0
            ) {
                $rutaSubida = $this->procesarArchivoSubido($archivosModulo, $indice);
                if ($rutaSubida) {
                    $contenidoTipo = 'archivo';
                    $contenidoUrl = $rutaSubida;
                }
            }
            if(is_null($contenidoUrl)){
                $contenidoUrl = "";
            }

            $datosModulo = [
                "curso_id" => $cursoId,
                "titulo" => $modulo["titulo"],
                "descripcion" => $modulo["descripcion"],
                "tipo" => $this->detectarTipoRecurso($contenidoUrl),
                "url" => $contenidoUrl,
                "orden" => $i + 1
            ];

            if (!$this->modeloInstancia->guardarModulos($datosModulo)) {
                echo "<script>alert('⚠️ Error al guardar un módulo'); window.history.back();</script>";
                return;
            }

            $i++;
        }
        echo "<script>
            alert('✅ Curso guardado exitosamente');
            window.location.href = '/cursos';
        </script>";
    }

    public function editarCurso()
    {
        if (!$this->validarAdmin()) {
            $this->redireccionar("/login");
            exit;
        }

        global $request;
        $idCurso = $request->get('id');
        if (!$idCurso) {
            echo "<script>alert('⚠️ ID del curso no especificado'); window.history.back();</script>";
            return;
        }

        $curso = $this->modeloInstancia->get($idCurso);
        if (!$curso) {
            echo "<script>alert('⚠️ Curso no encontrado'); window.history.back();</script>";
            return;
        }

        $temas = $this->modeloInstancia->getTemasCurso($idCurso);
        $modulos = $this->modeloInstancia->getModulosCurso($idCurso);

        $titulo = "PAD - Editar Curso";
        require $this->viewsDir . 'editar-curso.view.php';
    }

    public function procesarEditarCurso()
    {
        if (!$this->validarAdmin()) {
            $this->redireccionar("/login");
            exit;
        }

        global $request;
        $idCurso = $request->get('id_curso');
        if (!$idCurso) {
            echo "<script>alert('⚠️ ID del curso no especificado'); window.history.back();</script>";
            return;
        }

        $curso = $this->modeloInstancia->get($idCurso);
        if (!$curso) {
            echo "<script>alert('⚠️ Curso no encontrado'); window.history.back();</script>";
            return;
        }

        $tituloCurso = $request->get("titulo");
        $descripcionCurso = $request->get("descripcion");
        $recomendaciones = $request->get("recomendaciones_json") ?? null;
        $nivel = $request->get("nivel");
        $duracion = (int) $request->get("duracion");

        // Imagen del curso (preservar anterior si no se sube una nueva)
        $rutaImagenCurso = $curso->campos['imagen'];
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $rutaSubidaImagen = $this->procesarArchivoSubido($_FILES['imagen']);
                if ($rutaSubidaImagen) {
                    $rutaImagenCurso = $rutaSubidaImagen;
                }
            } else {
                $this->logger->error("Error PHP al editar imagen de portada: " . $_FILES['imagen']['error']);
                echo "<script>alert('⚠️ Error al subir la imagen del curso (Código: " . $_FILES['imagen']['error'] . "). Comprueba el tamaño del archivo.'); window.history.back();</script>";
                return;
            }
        }

        $datosCurso = [
            'titulo' => $tituloCurso,
            'descripcion' => $descripcionCurso,
            'recomendaciones' => $recomendaciones,
            'nivel' => $nivel,
            'duracion' => $duracion,
            'imagen' => $rutaImagenCurso
        ];

        $this->modeloInstancia->actualizarCurso($idCurso, $datosCurso);

        // 1. Re-guardar Temario (borrar antiguos e insertar nuevos)
        $this->modeloInstancia->eliminarTemasCurso($idCurso);
        $temas = $request->get("temario") ?? [];
        foreach ($temas as $orden => $temaTitulo) {
            $temaDatos = [
                'curso_id' => $idCurso,
                'titulo' => $temaTitulo,
            ];
            $this->modeloInstancia->guardarTema($temaDatos);
        }

        // 2. Guardar/Actualizar Módulos
        $modulosForm = $request->get("modulos") ?? [];
        $archivosModulo = $_FILES['modulos'] ?? [];

        // Obtener los IDs de los módulos que se mantendrán para eliminar el resto
        $idsPreservados = [];
        foreach ($modulosForm as $mod) {
            if (!empty($mod['id'])) {
                $idsPreservados[] = (int)$mod['id'];
            }
        }

        // Eliminar módulos removidos en la vista (y su progreso)
        $this->modeloInstancia->eliminarModulosCursoExcepto($idCurso, $idsPreservados);

        $i = 0;
        foreach ($modulosForm as $indice => $modulo) {
            $contenidoUrl = null;

            // Link al contenido
            if (!empty($modulo['link'])) {
                $contenidoUrl = $modulo['link'];
            }
            // Archivo subido
            elseif (
                !empty($archivosModulo['name'][$indice]['archivo']) &&
                $archivosModulo['error'][$indice]['archivo'] === 0
            ) {
                $rutaSubida = $this->procesarArchivoSubido($archivosModulo, $indice);
                if ($rutaSubida) {
                    $contenidoUrl = $rutaSubida;
                }
            }

            // Si no se especificó un nuevo archivo/link, usar la url existente
            if (is_null($contenidoUrl)) {
                $contenidoUrl = $modulo['url_existente'] ?? "";
            }

            $datosModulo = [
                "curso_id" => $idCurso,
                "titulo" => $modulo["titulo"],
                "descripcion" => $modulo["descripcion"],
                "tipo" => $this->detectarTipoRecurso($contenidoUrl),
                "url" => $contenidoUrl,
                "orden" => $i + 1
            ];

            if (!empty($modulo['id'])) {
                // Actualizar módulo existente
                $this->modeloInstancia->actualizarModulo((int)$modulo['id'], $datosModulo);
            } else {
                // Crear nuevo módulo
                $this->modeloInstancia->guardarModulos($datosModulo);
            }

            $i++;
        }

        echo "<script>
            alert('✅ Curso editado exitosamente');
            window.location.href = '/curso?id={$idCurso}';
        </script>";
        exit;
    }

    public function modeloIA()
    {
        header("Content-Type: application/json");

        $input = json_decode(file_get_contents("php://input"), true);

        $titulo = trim($input["titulo"] ?? "");
        $descripcion = trim($input["descripcion"] ?? "");
        $temario = $input["temario"] ?? [];

        if (!$titulo || !$descripcion || empty($temario)) {
            echo json_encode(["error" => "Faltan datos para procesar."]);
            return;
        }

        global $config, $log;

        // Instanciar el servicio desacoplado de IA
        $iaService = new \PAW\src\Core\Services\IAService($config);
        if (isset($log)) {
            $iaService->setLogger($log);
        }

        // Obtener recomendaciones
        $resultado = $iaService->obtenerRecomendaciones($titulo, $descripcion, $temario);

        $recomendaciones = array_filter($resultado["recomendaciones"] ?? [], function($rec) {
            return isset($rec["tipo"]) && isset($rec["titulo"]);
        });

        echo json_encode([
            "recomendaciones" => array_values($recomendaciones),
            "fallback" => $resultado["fallback"] ?? false
        ]);
    }

    public function detectarTipoRecurso(string $rutaOUrl): string
    {
        if (empty($rutaOUrl)) {
            return 'vacio';
        }

        // 1) YouTube
        if (strpos($rutaOUrl, 'youtube.com/watch?v=') !== false || strpos($rutaOUrl, 'youtu.be/') !== false) {
            return 'youtube';
        }

        // 2) URL externa
        if (filter_var($rutaOUrl, FILTER_VALIDATE_URL)) {
            $ext = strtolower(pathinfo(parse_url($rutaOUrl, PHP_URL_PATH), PATHINFO_EXTENSION));

            if ($ext === 'pdf')
                return 'pdf';
            if (in_array($ext, ['mp3', 'wav', 'ogg']))
                return 'audio';
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                return 'imagen';

            return 'url'; // URL externa genérica
        }

        // 3) Ruta interna (archivo subido al servidor)
        $ext = strtolower(pathinfo($rutaOUrl, PATHINFO_EXTENSION));

        if ($ext === 'pdf')
            return 'pdf';
        if (in_array($ext, ['mp3', 'wav', 'ogg']))
            return 'audio';
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
            return 'imagen';

        return 'archivo'; // Otro tipo de archivo (ej: zip, docx...)
    }

    public function embedRecurso(string $tipo, string $rutaOUrl): string
    {
        if ($tipo === 'vacio') {
            return '<p>No hay recurso para esta unidad.</p>';
        }

        // Sanitizar de forma preventiva para prevenir la inyección de atributos HTML maliciosos
        $rutaOUrlSegura = htmlspecialchars($rutaOUrl, ENT_QUOTES, 'UTF-8');

        switch ($tipo) {
            case 'youtube':
                // Soporte para ambos formatos
                if (strpos($rutaOUrl, 'youtube.com/watch?v=') !== false) {
                    parse_str(parse_url($rutaOUrl, PHP_URL_QUERY), $params);
                    $videoId = htmlspecialchars($params['v'] ?? '', ENT_QUOTES, 'UTF-8');
                } else {
                    $videoId = htmlspecialchars(basename(parse_url($rutaOUrl, PHP_URL_PATH)), ENT_QUOTES, 'UTF-8');
                }
                return "<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/{$videoId}\" frameborder=\"0\" allowfullscreen></iframe>";

            case 'pdf':
                return "<embed src=\"{$rutaOUrlSegura}\" type=\"application/pdf\" width=\"100%\" height=\"600px\" />";

            case 'audio':
                return "<audio controls src=\"{$rutaOUrlSegura}\">Tu navegador no soporta audio.</audio>";

            case 'imagen':
                return "<img src=\"{$rutaOUrlSegura}\" alt=\"Recurso imagen\" style=\"max-width:100%;\" />";

            case 'url':
                return "
                <div class=\"descargar-recurso-card\">
                    <div class=\"recurso-info\">
                        <div class=\"recurso-icon-wrapper url-type\">
                            <i class=\"fa-solid fa-link\"></i>
                        </div>
                        <div>
                            <span class=\"recurso-filename\">Enlace externo</span>
                            <span class=\"recurso-subtitle\">Recurso complementario en la web</span>
                        </div>
                    </div>
                    <a href=\"{$rutaOUrlSegura}\" target=\"_blank\" rel=\"noopener\" class=\"btn-descargar-recurso\"><i class=\"fa-solid fa-up-right-from-square\"></i> Abrir enlace</a>
                </div>";

            case 'archivo':
            default:
                $nombreArchivo = basename($rutaOUrl);
                $ext = strtolower(pathinfo($rutaOUrl, PATHINFO_EXTENSION));
                
                // Determinar el ícono adecuado según la extensión
                $iconClass = 'fa-file-arrow-down';
                if (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'])) {
                    $iconClass = 'fa-file-zipper';
                } elseif (in_array($ext, ['doc', 'docx'])) {
                    $iconClass = 'fa-file-word';
                } elseif (in_array($ext, ['xls', 'xlsx'])) {
                    $iconClass = 'fa-file-excel';
                } elseif (in_array($ext, ['ppt', 'pptx'])) {
                    $iconClass = 'fa-file-powerpoint';
                } elseif (in_array($ext, ['txt', 'md'])) {
                    $iconClass = 'fa-file-lines';
                }
                
                return "
                <div class=\"descargar-recurso-card\">
                    <div class=\"recurso-info\">
                        <div class=\"recurso-icon-wrapper file-type\">
                            <i class=\"fa-solid {$iconClass}\"></i>
                        </div>
                        <div>
                            <span class=\"recurso-filename\">" . htmlspecialchars($nombreArchivo, ENT_QUOTES, 'UTF-8') . "</span>
                            <span class=\"recurso-subtitle\">Archivo de recursos para descargar</span>
                        </div>
                    </div>
                    <a href=\"{$rutaOUrlSegura}\" download class=\"btn-descargar-recurso\"><i class=\"fa-solid fa-download\"></i> Descargar archivo</a>
                </div>";
        }
    }

    public function agregarComentario()
    {
        $this->validarSesion();
        global $request;
        
        $cursoId = $request->get("curso_id");
        $contenido = trim($request->get("contenido"));
        $usuarioId = $_SESSION["usuario"]["id"];
        
        if (!empty($contenido)) {
            $datosComentario = [
                "curso_id" => $cursoId,
                "usuario_id" => $usuarioId,
                "contenido" => $contenido
            ];
            $this->modeloInstancia->guardarComentario($datosComentario);
        }
        
        $this->redireccionar("/curso?id=" . urlencode($cursoId));
    }

    private function procesarArchivoSubido(array $files, $key = null): ?string
    {
        $name = $key !== null ? ($files['name'][$key]['archivo'] ?? null) : ($files['name'] ?? null);
        $tmpName = $key !== null ? ($files['tmp_name'][$key]['archivo'] ?? null) : ($files['tmp_name'] ?? null);
        $error = $key !== null ? ($files['error'][$key]['archivo'] ?? null) : ($files['error'] ?? null);

        if (!$name || $error !== 0) {
            return null;
        }

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        // Lista negra de extensiones prohibidas para evitar vulnerabilidad de Ejecución Remota de Código (RCE)
        $extensionesProhibidas = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8', 'phar', 'sh', 'exe', 'cgi', 'pl', 'jsp', 'asp', 'aspx', 'fcgi'];
        if (in_array($ext, $extensionesProhibidas)) {
            $ext = 'txt'; // Desarmar el script convirtiéndolo en texto plano inocuo
            $name = pathinfo($name, PATHINFO_FILENAME) . '.txt';
        }

        // --- MIGRACIÓN A CLOUDINARY ---
        // Intentar subir a Cloudinary si está configurado en el .env
        $cloudinary = new CloudinaryService($this->config, $this->logger);
        $urlNube = $cloudinary->subirArchivo($tmpName, $name);
        
        if ($urlNube) {
            return $urlNube; // Retorna la URL de internet segura
        }

        // --- FALLBACK LOCAL ---
        // Si falló Cloudinary o no estaba configurado, guardar localmente en el volumen del disco del contenedor
        $nombreUnico = uniqid('file_', true) . '_' . md5($name) . '.' . $ext;
        $carpetaImagenes = __DIR__ . '/../../../public/uploads/';
        $destino = $carpetaImagenes . $nombreUnico;

        if (move_uploaded_file($tmpName, $destino)) {
            return '/uploads/' . $nombreUnico;
        }

        return null;
    }
}
