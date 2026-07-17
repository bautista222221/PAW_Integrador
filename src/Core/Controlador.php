<?php

namespace PAW\src\Core;

use PAW\src\Core\Modelo;
use PAW\src\Core\Database\QueryBuilder;

class Controlador{
    public string $viewsDir;

    protected Config $config;
    public array $menu;

    public ?string $modelo = null;
    protected ?Modelo $modeloInstancia = null;

    public function __construct(Config $config)
    {
        global $connection;
        $this->config = $config;
        $this->viewsDir = __DIR__ . "/../App/views/";
        $this->menu = [
            [
                "href" => "/",
                "name" => "Inicio"
            ],
            [
                "href" => "/cursos",
                "name" => "Cursos"
            ],
            [
                "href" => "/login",
                "name" => "Iniciar Sesion"
            ],
            [
                "href" => "/register",
                "name" => "Registrarse"
            ],
            [
                "href" => "/user-profile",
                "name" => "Perfil de Usuario"
            ]
        ];
        if(!is_null($this->modelo)){
            $qb = new QueryBuilder($connection);
            $model = new $this->modelo;
            $model->setQueryBuilder($qb);
            $this->setModelo($model);
        }
    }
    public function setModelo(Modelo $modelo){
        $this->modeloInstancia = $modelo;
        
    }

    public function redireccionar(string $url)
    {
        if (!headers_sent()) {
            header("Location: " . $url);
            exit;
        } else {
            echo "<script>window.location.href = '" . $url . "';</script>";
            exit;
        }
    }
}