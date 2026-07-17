<?php

namespace PAW\src\App\Modelos;

use PAW\src\Core\Modelo;
use PAW\src\App\Modelos\Usuario;

class ColeccionUsuarios extends Modelo
{
    public $table = 'usuarios';

    public function crear($datos)
    {
        return $this->queryBuilder->insert($this->table, $datos);
    }

    public function autenticar($email, $password)
    {
        $resultado = $this->queryBuilder->select($this->table, ["correo" => $email]);

        if (empty($resultado)) {
            return null;
        }

        $usuario = new Usuario;
        $usuario->setQueryBuilder($this->queryBuilder);
        $usuario->set($resultado[0]);

        // Intentar autenticar con hash seguro
        if (password_verify($password, $usuario->campos['password'])) {
            return $usuario;
        }

        // Fallback y migración automática para contraseñas antiguas en texto plano
        if ($password === $usuario->campos['password']) {
            $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
            $this->actualizar($usuario->campos['id'], ['password' => $nuevoHash]);
            $usuario->campos['password'] = $nuevoHash;
            return $usuario;
        }

        return null;
    }

    public function actualizar($id, $datos)
    {
        return $this->queryBuilder->update($this->table, $datos, ["id" => $id]);
    }

    public function existeEmail($email)
    {
        $resultado = $this->queryBuilder->select($this->table, ["correo" => $email]);
        return !empty($resultado);
    }
}