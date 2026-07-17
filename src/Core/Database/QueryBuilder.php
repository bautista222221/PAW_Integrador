<?php

namespace PAW\src\Core\Database;

use PAW\src\Core\Traits\Loggable;
use PDO;

class QueryBuilder
{
    use Loggable;
    private PDO $pdo;
    
    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
    }

    public function select(string $tabla, array $parametros = []): array {
        $condiciones = [];
        $binds = [];

        foreach ($parametros as $campo => $valor) {
            $condiciones[] = "$campo = :$campo";
            $binds[":$campo"] = $valor;
        }

        $where = count($condiciones) > 0 ? implode(" AND ", $condiciones) : "1 = 1";

        $query = "SELECT * FROM {$tabla} WHERE {$where}";
        $stmt = $this->pdo->prepare($query);

        foreach ($binds as $clave => $valor) {
            $tipo = is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($clave, $valor, $tipo);
        }

        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function selectRaw(string $sql, array $parametros = []): array
    {
        $stmt = $this->pdo->prepare($sql);

        foreach ($parametros as $clave => $valor) {
            $tipo = is_int($valor) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindValue(":{$clave}", $valor, $tipo);
        }

        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute();

        return $stmt->fetchAll();
    }


    public function insert($tabla, $valores){
        $campos = array_keys($valores);
        $placeholders = array_map(function($campo) {
            return ":$campo";
        }, $campos);

        $query = "INSERT INTO {$tabla} (" . implode(", ", $campos) . ") VALUES (" . implode(", ", $placeholders) . ")";
        $stmt = $this->pdo->prepare($query);

        foreach ($valores as $campo => $valor) {
            if (is_int($valor)) {
                $tipo = PDO::PARAM_INT;
            } elseif (is_bool($valor)) {
                $tipo = PDO::PARAM_BOOL;
            } elseif (is_null($valor)) {
                $tipo = PDO::PARAM_NULL;
            } else {
                $tipo = PDO::PARAM_STR;
            }

            $stmt->bindValue(":$campo", $valor, $tipo);
        }
        return $stmt->execute();
    }

    public function insertConReturnId($tabla, $valores){
        $campos = array_keys($valores);
        $placeholders = array_map(function($campo) {
            return ":$campo";
        }, $campos);

        $query = "INSERT INTO {$tabla} (" . implode(", ", $campos) . ") VALUES (" . implode(", ", $placeholders) . ")";
        $stmt = $this->pdo->prepare($query);

        foreach ($valores as $campo => $valor) {
            $tipo = is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(":$campo", $valor, $tipo);
        }
        if ($stmt->execute()) {
            return $this->pdo->lastInsertId(); // 👈 Esta línea te devuelve el ID insertado
        } else {
            return false;
        }
    }

    public function update($tabla, $valores, $condiciones){
        $set = [];
        foreach (array_keys($valores) as $campo) {
            $set[] = "$campo = :$campo";
        }

        $where = [];
        foreach (array_keys($condiciones) as $cond) {
            $where[] = "$cond = :cond_$cond";
        }

        $query = "UPDATE {$tabla} SET " . implode(", ", $set) . " WHERE " . implode(" AND ", $where);
        $stmt = $this->pdo->prepare($query);

        // Bind de valores
        foreach ($valores as $campo => $valor) {
            $tipo = is_int($valor) ? PDO::PARAM_INT :
                    (is_bool($valor) ? PDO::PARAM_BOOL :
                    (is_null($valor) ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindValue(":$campo", $valor, $tipo);
        }

        // Bind de condiciones
        foreach ($condiciones as $campo => $valor) {
            $tipo = is_int($valor) ? PDO::PARAM_INT :
                    (is_bool($valor) ? PDO::PARAM_BOOL :
                    (is_null($valor) ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindValue(":cond_$campo", $valor, $tipo);
        }

        return $stmt->execute();
    }

    public function delete(string $tabla, array $condiciones = []): bool
    {
        $whereCond = [];
        $binds = [];

        foreach ($condiciones as $campo => $valor) {
            $whereCond[] = "$campo = :$campo";
            $binds[":$campo"] = $valor;
        }

        $where = count($whereCond) > 0 ? implode(" AND ", $whereCond) : "1 = 1";
        $query = "DELETE FROM {$tabla} WHERE {$where}";
        $stmt = $this->pdo->prepare($query);

        foreach ($binds as $clave => $valor) {
            $tipo = is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($clave, $valor, $tipo);
        }

        return $stmt->execute();
    }
    public function count(string $tabla, array $parametros = []): int
    {
        $condiciones = [];
        $binds = [];

        // Filtro por búsqueda textual
        if (isset($parametros['consulta'])) {
            $condiciones[] = "(titulo LIKE :consulta OR autor LIKE :consulta)";
            $binds[':consulta'] = '%' . $parametros['consulta'] . '%';
        }

        // Filtro por IDs
        if (isset($parametros['ids']) && is_array($parametros['ids']) && count($parametros['ids']) > 0) {
            $placeholders = [];
            foreach ($parametros['ids'] as $i => $id) {
                $ph = ":id_$i";
                $placeholders[] = $ph;
                $binds[$ph] = $id;
            }
            $condiciones[] = "id IN (" . implode(",", $placeholders) . ")";
        }

        // Otras condiciones personalizadas
        if (isset($parametros['condiciones']) && is_array($parametros['condiciones'])) {
            foreach ($parametros['condiciones'] as $cond) {
                $condiciones[] = $cond;
            }
        }

        $where = count($condiciones) > 0 ? " WHERE " . implode(" AND ", $condiciones) : "";

        $query = "SELECT COUNT(*) as total FROM {$tabla}{$where}";
        $stmt = $this->pdo->prepare($query);

        foreach ($binds as $clave => $valor) {
            $tipo = is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($clave, $valor, $tipo);
        }

        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$resultado['total'];
    }
}