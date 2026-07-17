<?php

namespace PAW\src\App\Modelos;

use PAW\src\Core\Exceptions\InvalidValueFormatException;
use PAW\src\Core\Modelo;

class Curso extends Modelo
{
    public $table = 'cursos';

    public $campos = [
        "id" => null,
        "titulo" => null,
        "descripcion" => null,
        "recomendaciones" => null,
        "creado_por" => null,
        "fecha_creacion" => null,
        "activo" => null,
        "nivel" => null,
        "duracion" => null,
        "imagen" => null,
    ];

    public function setId($id)
    {
        if (!is_numeric($id) || intval($id) < 0) {
            throw new InvalidValueFormatException("El ID debe ser un número entero positivo.");
        }
        $this->campos['id'] = intval($id);
    }

    public function setTitulo(string $titulo)
    {
        $titulo = trim($titulo);
        if (strlen($titulo) === 0) {
            throw new InvalidValueFormatException("El título del curso no puede estar vacío.");
        }
        if (strlen($titulo) > 255) {
            throw new InvalidValueFormatException("El título del curso no puede tener más de 255 caracteres.");
        }
        $this->campos['titulo'] = $titulo;
    }

    public function setDescripcion(string $descripcion)
    {
        $descripcion = trim($descripcion);
        if (strlen($descripcion) === 0) {
            throw new InvalidValueFormatException("La descripción no puede estar vacía.");
        }
        $this->campos['descripcion'] = $descripcion;
    }

    public function setRecomendaciones(?string $recomendaciones)
    {
        if (empty($recomendaciones)) {
            $this->campos['recomendaciones'] = [];
            return;
        }

        // Intentamos decodificar el string JSON a array asociativo
        $recs = json_decode($recomendaciones, true);

        // Verificamos que sea un array válido
        if (!is_array($recs)) {
            throw new InvalidValueFormatException("El formato de las recomendaciones no es un JSON válido.");
        }

        foreach ($recs as $rec) {
            if (!is_array($rec) || !isset($rec['tipo']) || !isset($rec['titulo'])) {
                throw new InvalidValueFormatException("Cada recomendación debe tener al menos 'tipo' y 'titulo'.");
            }
        }

        $this->campos['recomendaciones'] = $recs;
    }

    public function setCreado_por(int $creado_por)
    {
        if ($creado_por <= 0) {
            throw new InvalidValueFormatException("El ID del creador debe ser un entero positivo.");
        }
        $this->campos['creado_por'] = $creado_por;
    }

    public function setFecha_creacion(string $fecha)
    {
        $this->campos['fecha_creacion'] = $fecha;
    }

    public function setActivo(bool $activo)
    {
        $this->campos['activo'] = $activo;
    }

    public function setNivel(string $nivel)
    {
        $nivel = trim($nivel);
        if (strlen($nivel) === 0) {
            throw new InvalidValueFormatException("El nivel del curso no puede estar vacío.");
        }
        if (strlen($nivel) > 255) {
            throw new InvalidValueFormatException("El nivel del curso no puede tener más de 255 caracteres.");
        }
        $this->campos['nivel'] = $nivel;
    }

    public function setDuracion(int $duracion)
    {
        if ($duracion <= 0) {
            throw new InvalidValueFormatException("La duración debe ser un número entero positivo (en horas, semanas, etc.).");
        }
        $this->campos['duracion'] = $duracion;
    }

    public function setImagen(string $imagen)
    {
        $imagen = trim($imagen);
        if (strlen($imagen) === 0) {
            throw new InvalidValueFormatException("La ruta de la imagen no puede estar vacía.");
        }
        if (strlen($imagen) > 255) {
            throw new InvalidValueFormatException("La ruta de la imagen no puede tener más de 255 caracteres.");
        }
        $this->campos['imagen'] = $imagen;
    }

    public function set(array $valores)
    {
        foreach (array_keys($this->campos) as $campo) {
            if (!isset($valores[$campo])) {
                continue;
            }
            $metodo = "set" . ucfirst($campo);
            $this->$metodo($valores[$campo]);
        }
    }

    public function load($id){
        $parametros = ['id' => $id]; // convertimos a array
        $resultado = $this->queryBuilder->select($this->table, $parametros);
        $record = current($resultado);
        $this->set($record);
    }
}
