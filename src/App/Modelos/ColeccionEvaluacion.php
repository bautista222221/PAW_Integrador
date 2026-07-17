<?php

namespace PAW\src\App\Modelos;

use PAW\src\Core\Modelo;
use PAW\src\App\Modelos\Evaluacion;

class ColeccionEvaluacion extends Modelo
{
    public $tableEvaluacion = 'evaluaciones';
    public $tablePreguntas = 'preguntas';
    public $tableOpciones = 'opciones';


    public function get($id)
    {
        $evaluacion = new Evaluacion;
        $evaluacion->setQueryBuilder($this->queryBuilder);
        $evaluacion->load($id);
        return $evaluacion;
    }

    public function crear(array $datos): Evaluacion
    {
        $evaluacion = new Evaluacion();
        $evaluacion->setQueryBuilder($this->queryBuilder);
        $evaluacion->set($datos);
        $evaluacion->guardar();
        return $evaluacion;
    }

    public function crearEvaluacion(array $datos): ?int
    {
        return $this->queryBuilder->insertConReturnId($this->tableEvaluacion, $datos);
    }

    public function crearPregunta(array $datos): ?int
    {
        return $this->queryBuilder->insertConReturnId($this->tablePreguntas, $datos);
    }

    public function crearOpcion(array $datos): bool
    {
        return $this->queryBuilder->insertConReturnId($this->tableOpciones, $datos);
    }

    public function buscarPorId(string $id): ?array
    {
        if (empty($id)) {
            return null;
        }

        $resultados = $this->queryBuilder->select('cursos', ['id' => $id]);
        return $resultados[0] ?? null;
    }

    public function obtenerEvaluacionPorCurso(string $idCurso): ?array
    {
        $curso = $this->buscarPorId($idCurso);

        if (!$curso || !isset($curso['id'])) {
            return null;
        }

        $evaluaciones = $this->queryBuilder->select('evaluaciones', ['curso_id' => $curso['id']]);
        return $evaluaciones[0] ?? null;
    }

    public function obtenerEvaluacionConPreguntasPorCurso(string $idCurso): ?array
    {
        if (empty($idCurso)) {
            return null;
        }

        // Buscar evaluación directamente por el curso_id recibido
        $evaluaciones = $this->queryBuilder->select('evaluaciones', ['curso_id' => $idCurso]);
        if (!empty($evaluaciones)) {
            usort($evaluaciones, function($a, $b) {
                return $b['id'] <=> $a['id'];
            });
        }
        $evaluacion = $evaluaciones[0] ?? null;

        if (!$evaluacion) {
            return null;
        }

        $preguntas = $this->queryBuilder->select('preguntas', ['id_evaluacion' => $evaluacion['id']]);

        foreach ($preguntas as &$pregunta) {
            $opciones = $this->queryBuilder->select('opciones', ['id_pregunta' => $pregunta['id']]);

            foreach ($opciones as $opcion) {
                if (!empty($opcion['es_correcta'])) {
                    $pregunta['respuesta_correcta'] = $opcion['id'];
                    break;
                }
            }

            $pregunta['opciones'] = $opciones;
        }

        $evaluacion['preguntas'] = $preguntas;
        return $evaluacion;
    }

    public function existeInscripcion($usuarioId, $cursoId): bool
    {
        $resultados = $this->queryBuilder->select('inscripciones', [
            'usuario_id' => $usuarioId,
            'curso_id' => $cursoId
        ]);

        return !empty($resultados);
    }

    public function actualizarEvaluacion(int $idEvaluacion, array $datos): bool
    {
        return $this->queryBuilder->update('evaluaciones', $datos, ['id' => $idEvaluacion]);
    }

    public function eliminarPreguntasDeEvaluacion(int $idEvaluacion): bool
    {
        return $this->queryBuilder->delete('preguntas', ['id_evaluacion' => $idEvaluacion]);
    }

    public function guardarAprobacion(int $usuarioId, int $cursoId, int $nota): bool
    {
        return $this->queryBuilder->update('inscripciones', [
            'nota' => $nota,
            'aprobado' => true,
            'fecha_aprobado' => date('Y-m-d H:i:s')
        ], [
            'usuario_id' => $usuarioId,
            'curso_id' => $cursoId
        ]);
    }

    public function obtenerCursosAprobados(int $usuarioId): array
    {
        $sql = "SELECT i.*, c.titulo as curso_titulo
                FROM inscripciones i
                JOIN cursos c ON i.curso_id = c.id
                WHERE i.usuario_id = :usuario_id AND i.aprobado = true
                ORDER BY i.fecha_aprobado DESC";
        return $this->queryBuilder->selectRaw($sql, ['usuario_id' => $usuarioId]);
    }

    public function obtenerInscripcionAprobada(int $usuarioId, int $cursoId): ?array
    {
        $sql = "SELECT i.*, c.titulo as curso_titulo
                FROM inscripciones i
                JOIN cursos c ON i.curso_id = c.id
                WHERE i.usuario_id = :usuario_id AND i.curso_id = :curso_id AND i.aprobado = true";
        $resultados = $this->queryBuilder->selectRaw($sql, [
            'usuario_id' => $usuarioId,
            'curso_id' => $cursoId
        ]);
        return !empty($resultados) ? $resultados[0] : null;
    }
}