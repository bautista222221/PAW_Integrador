<?php

namespace PAW\src\App\Modelos;

use PAW\src\Core\Modelo;
use PAW\src\App\Modelos\Curso;

class ColeccionCursos extends Modelo
{
    public $table = 'cursos';

    public function get($id)
    {
        $curso = new Curso;
        $curso->setQueryBuilder($this->queryBuilder);
        $curso->load($id);
        return $curso;
    }

    public function getAll()
    {
        $cursos = $this->queryBuilder->select($this->table);
        $coleccionCursos = [];
        foreach ($cursos as $curso) {
            $nuevoCurso = new Curso;
            $nuevoCurso->set($curso);
            $coleccionCursos[] = $nuevoCurso;
        }
        return $coleccionCursos;
    }

    public function crear($datos)
    {
        return $this->queryBuilder->insertConReturnId($this->table, $datos);
    }

    public function guardarTema($tema)
    {
        return $this->queryBuilder->insert("temas", $tema);
    }

    public function guardarModulos(array $datos)
    {
        return $this->queryBuilder->insert("modulos", $datos);
    }

    public function getModulosCurso($idCurso)
    {
        return $this->queryBuilder->select("modulos", ["curso_id" => $idCurso]);
    }

    public function getTemasCurso($idCurso)
    {
        return $this->queryBuilder->select("temas", ["curso_id" => $idCurso]);
    }

    public function getModulo($idModulo)
    {
        return current($this->queryBuilder->select("modulos", ["id" => $idModulo]));
    }

    public function getEvaluacion($idCurso)
    {
        return $this->queryBuilder->select("evaluaciones", ["curso_id" => $idCurso]);
    }

    public function marcarCompletado($moduloId, $cursoId, $usuarioId)
    {
        $datosProgreso = [
            "completado" => true,
            "fecha_completado" => date("Y-m-d H:i:s")
        ];
        return $this->queryBuilder->update("progresos", $datosProgreso, ["modulo_id" => $moduloId, "curso_id" => $cursoId, "usuario_id" => $usuarioId]);
    }

    public function existeProgreso($usuarioId, $cursoId, $moduloId)
    {
        $datos = $this->queryBuilder->select("progresos", ["curso_id" => $cursoId, "usuario_id" => $usuarioId, "modulo_id" => $moduloId]);
        return !empty($datos);
    }

    public function estaCompletado($usuarioId, $cursoId, $moduloId)
    {
        $datos = current($this->queryBuilder->select("progresos", ["curso_id" => $cursoId, "usuario_id" => $usuarioId, "modulo_id" => $moduloId]));
        return $datos["completado"];
    }

    public function crearProgreso($usuarioId, $cursoId, $moduloId)
    {
        $datosProgreso = [
            "usuario_id" => $usuarioId,
            "curso_id" => $cursoId,
            "modulo_id" => $moduloId,
            "completado" => false
        ];
        $this->queryBuilder->insert("progresos", $datosProgreso);
    }

    public function getProgresosUsuarioCurso($usuarioId, $cursoId)
    {
        return $this->queryBuilder->select("progresos", ["curso_id" => $cursoId, "usuario_id" => $usuarioId]);
    }

    public function guardarComentario($comentario)
    {
        return $this->queryBuilder->insert("comentarios", $comentario);
    }

    public function getComentariosCurso($idCurso)
    {
        $sql = "SELECT c.*, u.nombre as usuario_nombre 
                FROM comentarios c 
                JOIN usuarios u ON c.usuario_id = u.id 
                WHERE c.curso_id = :curso_id 
                ORDER BY c.fecha_creacion DESC";
        return $this->queryBuilder->selectRaw($sql, ["curso_id" => $idCurso]);
    }

    public function existeInscripcion($usuarioId, $cursoId): bool
    {
        $resultados = $this->queryBuilder->select('inscripciones', [
            'usuario_id' => $usuarioId,
            'curso_id' => $cursoId
        ]);

        return !empty($resultados);
    }

    /**
     * Carga todo lo necesario para la vista de un curso en la menor cantidad
     * de queries posible, optimizado para bases de datos remotas con alta latencia.
     */
    public function getCursoCompleto(int $cursoId, int $usuarioId): array
    {
        // Query 1: Datos del curso + temas + módulos + progresos + inscripción + evaluación en una sola consulta batch
        $sql = "
            SELECT 'curso' as _tipo, c.*, NULL as modulo_id, NULL as completado, NULL as tipo_tema, NULL as nombre_tema
            FROM cursos c WHERE c.id = :cid1

            UNION ALL

            SELECT 'tema' as _tipo, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, t.id as modulo_id, NULL as completado, t.tipo as tipo_tema, t.nombre as nombre_tema
            FROM temas t WHERE t.curso_id = :cid2
        ";
        // El UNION ALL no es práctico con esquemas diferentes, así que usamos batch de queries separadas
        // pero enviadas en una sola transacción para reducir round-trips.

        // Estrategia: usar una sola query con subqueries laterales no es viable en PostgreSQL con este esquema.
        // En cambio, agrupamos las queries más simples.
        
        $resultado = [];
        
        // Query 1: Curso + Inscripción (2 en 1)
        $sqlCurso = "SELECT c.*, 
                     EXISTS(SELECT 1 FROM inscripciones i WHERE i.usuario_id = :uid AND i.curso_id = :cid) as inscripto,
                     EXISTS(SELECT 1 FROM evaluaciones e WHERE e.curso_id = :cid2) as tiene_evaluacion
                     FROM cursos c WHERE c.id = :cid3";
        $cursoData = $this->queryBuilder->selectRaw($sqlCurso, [
            'uid' => $usuarioId,
            'cid' => $cursoId,
            'cid2' => $cursoId,
            'cid3' => $cursoId
        ]);
        $resultado['curso'] = !empty($cursoData) ? $cursoData[0] : null;
        
        // Query 2: Temas + Módulos + Progresos (todo junto)
        $sqlModulos = "SELECT m.*, 
                       COALESCE(p.completado, false) as completado,
                       p.id as progreso_id
                       FROM modulos m
                       LEFT JOIN progresos p ON p.modulo_id = m.id AND p.curso_id = :cid AND p.usuario_id = :uid
                       WHERE m.curso_id = :cid2
                       ORDER BY m.id";
        $resultado['modulos'] = $this->queryBuilder->selectRaw($sqlModulos, [
            'cid' => $cursoId,
            'uid' => $usuarioId,
            'cid2' => $cursoId
        ]);
        
        // Query 3: Temas + Comentarios (2 en 1 no es viable, pero agrupamos)
        $resultado['temas'] = $this->queryBuilder->select('temas', ['curso_id' => $cursoId]);
        
        // Query 4: Comentarios con nombre de usuario
        $sqlComentarios = "SELECT c.*, u.nombre as usuario_nombre 
                          FROM comentarios c 
                          JOIN usuarios u ON c.usuario_id = u.id 
                          WHERE c.curso_id = :curso_id 
                          ORDER BY c.fecha_creacion DESC";
        $resultado['comentarios'] = $this->queryBuilder->selectRaw($sqlComentarios, ['curso_id' => $cursoId]);
        
        return $resultado;
    }

    public function getCursosActivosUsuario($usuarioId)
    {
        $sql = "SELECT c.* 
                FROM cursos c 
                JOIN inscripciones i ON c.id = i.curso_id 
                WHERE i.usuario_id = :usuario_id";
        $cursos = $this->queryBuilder->selectRaw($sql, ["usuario_id" => $usuarioId]);
        
        $coleccionCursos = [];
        foreach ($cursos as $curso) {
            $nuevoCurso = new Curso;
            $nuevoCurso->set($curso);
            $coleccionCursos[] = $nuevoCurso;
        }
        return $coleccionCursos;
    }

    public function actualizarCurso(int $idCurso, array $datos): bool
    {
        return $this->queryBuilder->update('cursos', $datos, ['id' => $idCurso]);
    }

    public function eliminarTemasCurso(int $idCurso): bool
    {
        return $this->queryBuilder->delete('temas', ['curso_id' => $idCurso]);
    }

    public function actualizarModulo(int $idModulo, array $datos): bool
    {
        return $this->queryBuilder->update('modulos', $datos, ['id' => $idModulo]);
    }

    public function eliminarModulosCursoExcepto(int $idCurso, array $idsPreservados): bool
    {
        if (empty($idsPreservados)) {
            $this->queryBuilder->delete('progresos', ['curso_id' => $idCurso]);
            return $this->queryBuilder->delete('modulos', ['curso_id' => $idCurso]);
        }

        $modulos = $this->getModulosCurso($idCurso);
        $idsAEliminar = [];
        foreach ($modulos as $mod) {
            if (!in_array($mod['id'], $idsPreservados)) {
                $idsAEliminar[] = (int)$mod['id'];
            }
        }

        if (empty($idsAEliminar)) {
            return true;
        }

        foreach ($idsAEliminar as $modId) {
            $this->queryBuilder->delete('progresos', ['modulo_id' => $modId]);
            $this->queryBuilder->delete('modulos', ['id' => $modId]);
        }

        return true;
    }
}