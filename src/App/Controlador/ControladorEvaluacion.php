<?php

namespace PAW\src\App\Controlador;

use PAW\src\Core\Controlador;
use PAW\src\App\Modelos\ColeccionEvaluacion;

class ControladorEvaluacion extends Controlador
{
    public ?string $modelo = ColeccionEvaluacion::class;

    public function validarAdmin(): bool
    {
        if (!isset($_SESSION['usuario'])) {
            return false;
        }
        $tipo_usuario = $_SESSION['usuario']['tipo_usuario'] ?? null;
        return $tipo_usuario === 'admin';
    }
    public function validarSesion()
    {
        if (!isset($_SESSION['usuario'])) {
            header("Location: /login");
            exit;
        }
    }

    public function agregarEvaluacion()
    {
        if (!$this->validarAdmin()) {
            return;
        }

        $idCurso = $_GET['id'] ?? null;
        $titulo = "PAD - Agregar Evaluacion";

        require $this->viewsDir . 'agregar-evaluacion.view.php';
    }


    public function procesarAgregarEvaluacion()
    {
        if (!$this->validarAdmin()) {
            echo "<script>alert('⚠️ Acceso no autorizado'); window.history.back();</script>";
            return;
        }

        global $request;

        $tituloEvaluacion = $request->get("titulo");
        $idCurso = $request->get('id_curso');

        if (!$idCurso) {
            echo "<script>alert('⚠️ ID del curso no especificado');</script>";
            return;
        }

        $preguntas = $request->get("preguntas"); // Esto depende de cómo estés enviando las preguntas

        if (empty($preguntas)) {
            echo "<script>alert('⚠️ No se puede crear una evaluación sin preguntas. Por favor, agrega al menos una pregunta.'); window.history.back();</script>";
            return;
        }

        // 1. Insertar evaluación
        $datosEvaluacion = [
            'titulo' => $tituloEvaluacion,
            'curso_id' => $idCurso,
        ];
        $evaluacionId = $this->modeloInstancia->crearEvaluacion($datosEvaluacion);

        if (!$evaluacionId) {
            echo "<script>alert('⚠️ Error al crear la evaluación'); window.history.back();</script>";
            return;
        }

        // 2. Insertar preguntas y sus opciones
        foreach ($preguntas as $pregunta) {
            // Determinar enunciado real
            $enunciado = $pregunta['enunciado']; // por defecto

            if ($pregunta['tipo'] === 'completar') {
                // Si es completar, usar el campo correcto que viene desde el formulario
                $enunciado = $pregunta['opciones'][0]['enunciado'] ?? '';
            }

            $datosPregunta = [
                'id_evaluacion' => $evaluacionId,
                'enunciado' => $enunciado,
                'tipo' => $pregunta['tipo'],
                'palabra_correcta' => $pregunta['tipo'] === 'completar'
                    ? ($pregunta['opciones'][0]['respuesta_correcta'] ?? '')
                    : null,
            ];


            $preguntaId = $this->modeloInstancia->crearPregunta($datosPregunta);

            if (!$preguntaId) {
                echo "<script>alert('⚠️ Error al guardar una pregunta'); window.history.back();</script>";
                return;
            }

            // 3. Insertar opciones para cada pregunta
            foreach ($pregunta['opciones'] as $index => $opcion) {
                if (!isset($opcion['texto']) && $pregunta['tipo'] !== 'completar') {
                    continue; // evitar el error si no es completar y no hay 'texto'
                }
    
                // tu lógica de $datosOpcion...
                if ($pregunta['tipo'] === 'ordenar') {
                    $datosOpcion = [
                        'id_pregunta' => $preguntaId,
                        'texto' => $opcion['texto'],
                        'es_correcta' => 0,
                        'posicion_correcta' => $opcion['posicion'] ?? null,
                    ];
                } else if ($pregunta['tipo'] === 'multiple-choice') {
                    $esCorrecta = isset($pregunta['correcta']) && (int) $pregunta['correcta'] === $index ? 1 : 0;
                    $datosOpcion = [
                        'id_pregunta' => $preguntaId,
                        'texto' => $opcion['texto'],
                        'es_correcta' => $esCorrecta,
                    ];
                }
                else {
                    // Si no es un tipo que requiere opciones (como completar), salteamos
                    continue;
                }

                if (!$this->modeloInstancia->crearOpcion($datosOpcion)) {
                    echo "<script>alert('⚠️ Error al guardar una opción'); window.history.back();</script>";
                    return;
                }
            }
        }

        echo "<script>
        alert('✅ Evaluación creada exitosamente');
        window.location.href = '/curso?id={$idCurso}';
        </script>";
    }
    public function resolverEvaluacion()
    {
        $this->validarSesion();

        $idCurso = $_GET['curso'] ?? '';

        if (!$idCurso) {
            echo "<script>alert('⚠️ ID de curso no proporcionado'); window.history.back();</script>";
            return;
        }

        $usuarioId = $_SESSION["usuario"]["id"];
        if (!$this->modeloInstancia->existeInscripcion($usuarioId, $idCurso)) {
            header("Location: /curso?id=" . urlencode($idCurso));
            exit;
        }

        $evaluacion = $this->modeloInstancia->obtenerEvaluacionConPreguntasPorCurso($idCurso);


        if (!$evaluacion || empty($evaluacion['preguntas'])) {
            echo "<script>alert('⚠️ No se encontró una evaluación para este curso'); window.history.back();</script>";
            return;
        }

        $titulo = "PAD - Resolver Evaluación";
        require $this->viewsDir . 'resolver-evaluacion.view.php';
    }

    public function procesarResolverEvaluacion()
    {
        $this->validarSesion();

        $respuestas = $_POST['respuestas'] ?? [];
        $idCurso = $_POST['id_curso'] ?? null;

        if (!$idCurso) {
            echo "<script>alert('⚠️ Curso no especificado'); window.history.back();</script>";
            return;
        }

        $usuarioId = $_SESSION["usuario"]["id"];
        if (!$this->modeloInstancia->existeInscripcion($usuarioId, $idCurso)) {
            header("Location: /curso?id=" . urlencode($idCurso));
            exit;
        }

        $evaluacion = $this->modeloInstancia->obtenerEvaluacionConPreguntasPorCurso($idCurso);
        
        if (!$evaluacion || empty($evaluacion['preguntas'])) {
            echo "<script>alert('⚠️ No se pudo obtener la evaluación'); window.history.back();</script>";
            return;
        }

        $correctas = 0;
        $totalPreguntas = count($evaluacion['preguntas']);

        foreach ($evaluacion['preguntas'] as $index => $pregunta) {
            $userAnswer = $respuestas[$index] ?? null;
            if ($userAnswer === null) {
                continue;
            }

            if ($pregunta['tipo'] === 'multiple-choice') {
                if (isset($pregunta['respuesta_correcta']) && $userAnswer === $pregunta['respuesta_correcta']) {
                    $correctas++;
                }
            } elseif ($pregunta['tipo'] === 'completar') {
                $palabraCorrecta = trim($pregunta['palabra_correcta'] ?? '');
                if (strcasecmp(trim($userAnswer), $palabraCorrecta) === 0) {
                    $correctas++;
                }
            } elseif ($pregunta['tipo'] === 'ordenar') {
                $esCorrecto = true;
                foreach ($pregunta['opciones'] as $opcion) {
                    $opcionId = $opcion['id'];
                    $userPos = isset($userAnswer[$opcionId]) ? (int) $userAnswer[$opcionId] : null;
                    $correctPos = isset($opcion['posicion_correcta']) ? (int) $opcion['posicion_correcta'] : null;
                    if ($userPos !== $correctPos) {
                        $esCorrecto = false;
                        break;
                    }
                }
                if ($esCorrecto) {
                    $correctas++;
                }
            }
        }

        $puntuacion = round(($correctas / $totalPreguntas) * 10);

        // Si aprobó (puntuación > 6), guardar en inscripciones
        if ($puntuacion > 6) {
            $this->modeloInstancia->guardarAprobacion($usuarioId, $idCurso, (int)$puntuacion);
        }

        $_SESSION['resultado_evaluacion'] = [
            'curso_id' => $idCurso,
            'evaluacion_titulo' => $evaluacion['titulo'],
            'correctas' => $correctas,
            'total' => $totalPreguntas,
            'puntuacion' => $puntuacion
        ];

        header("Location: /resultado-evaluacion");
        exit;
    }

    public function resultadoEvaluacion()
    {
        $this->validarSesion();
        $resultado = $_SESSION['resultado_evaluacion'] ?? null;
        $titulo = "PAD - Resultados";
        unset($_SESSION['resultado_evaluacion']);
        require $this->viewsDir . 'resultados.view.php';
    }

    public function descargarCertificado()
    {
        $this->validarSesion();

        global $request;
        $idCurso = $request->get('curso');
        if (!$idCurso) {
            echo "<script>alert('⚠️ Curso no especificado'); window.history.back();</script>";
            return;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        $nombreUsuario = $_SESSION['usuario']['nombre'];

        $inscripcion = $this->modeloInstancia->obtenerInscripcionAprobada($usuarioId, $idCurso);

        if (!$inscripcion) {
            echo "<script>alert('⚠️ No se encontró un certificado aprobado para este curso.'); window.history.back();</script>";
            return;
        }

        $certificado = [
            'nombre_estudiante' => $nombreUsuario,
            'curso_titulo' => $inscripcion['curso_titulo'],
            'nota' => $inscripcion['nota'],
            'fecha_aprobado' => date('d/m/Y', strtotime($inscripcion['fecha_aprobado'])),
            'curso_id' => $idCurso
        ];

        $titulo = "PAD - Certificado";
        require $this->viewsDir . 'certificado.view.php';
    }

    public function editarEvaluacion()
    {
        $this->validarSesion();
        if (!$this->validarAdmin()) {
            header('HTTP/1.1 403 Forbidden');
            echo "Acceso denegado.";
            return;
        }

        global $request;
        $idCurso = $request->get('curso');
        if (!$idCurso) {
            echo "<script>alert('⚠️ ID del curso no especificado'); window.history.back();</script>";
            return;
        }

        $evaluacion = $this->modeloInstancia->obtenerEvaluacionConPreguntasPorCurso($idCurso);
        if (!$evaluacion) {
            echo "<script>alert('⚠️ No se encontró una evaluación para este curso para editar.'); window.history.back();</script>";
            return;
        }

        $titulo = "PAD - Editar Evaluación";
        require $this->viewsDir . 'editar-evaluacion.view.php';
    }

    public function procesarEditarEvaluacion()
    {
        $this->validarSesion();
        if (!$this->validarAdmin()) {
            header('HTTP/1.1 403 Forbidden');
            echo "Acceso denegado.";
            return;
        }

        global $request;

        $idEvaluacion = $request->get('id_evaluacion');
        $idCurso = $request->get('id_curso');
        $tituloEvaluacion = $request->get("titulo");
        $preguntas = $request->get("preguntas");

        if (!$idEvaluacion || !$idCurso) {
            echo "<script>alert('⚠️ Datos incompletos'); window.history.back();</script>";
            return;
        }

        if (empty($preguntas)) {
            echo "<script>alert('⚠️ No se puede guardar una evaluación sin preguntas.'); window.history.back();</script>";
            return;
        }

        // 1. Actualizar título de la evaluación
        $this->modeloInstancia->actualizarEvaluacion($idEvaluacion, ['titulo' => $tituloEvaluacion]);

        // 2. Eliminar preguntas anteriores (las opciones se borran en cascada en la DB)
        $this->modeloInstancia->eliminarPreguntasDeEvaluacion($idEvaluacion);

        // 3. Insertar las nuevas preguntas y opciones
        foreach ($preguntas as $pregunta) {
            $datosPregunta = [
                'id_evaluacion' => $idEvaluacion,
                'tipo' => $pregunta['tipo'],
                'enunciado' => $pregunta['enunciado'],
                'palabra_correcta' => ($pregunta['tipo'] === 'completar') ? ($pregunta['opciones'][0]['respuesta_correcta'] ?? null) : null
            ];

            if ($pregunta['tipo'] === 'completar') {
                $datosPregunta['enunciado'] = $pregunta['opciones'][0]['enunciado'] ?? '';
            }

            $preguntaId = $this->modeloInstancia->crearPregunta($datosPregunta);

            if ($pregunta['tipo'] === 'multiple-choice') {
                $correctaIndex = isset($pregunta['correcta']) ? (int) $pregunta['correcta'] : 0;
                foreach ($pregunta['opciones'] as $oIdx => $opcion) {
                    $datosOpcion = [
                        'id_pregunta' => $preguntaId,
                        'texto' => $opcion['texto'],
                        'es_correcta' => ($oIdx === $correctaIndex) ? 1 : 0,
                    ];
                    $this->modeloInstancia->crearOpcion($datosOpcion);
                }
            } elseif ($pregunta['tipo'] === 'ordenar') {
                foreach ($pregunta['opciones'] as $opcion) {
                    $datosOpcion = [
                        'id_pregunta' => $preguntaId,
                        'texto' => $opcion['texto'],
                        'es_correcta' => 0,
                        'posicion_correcta' => $opcion['posicion']
                    ];
                    $this->modeloInstancia->crearOpcion($datosOpcion);
                }
            }
        }

        echo "<script>
        alert('✅ Evaluación editada y guardada exitosamente');
        window.location.href = '/curso?id={$idCurso}';
        </script>";
        exit;
    }
}
