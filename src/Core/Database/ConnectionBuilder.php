<?php

namespace PAW\src\Core\Database;

use PDO;
use PDOException;
use PAW\src\Core\Config;
use PAW\src\Core\Traits\Loggable;

class ConnectionBuilder{
    use Loggable;
    public function make(Config $config): PDO {
        try{
            $adapter = $config->get('DB_ADAPTER');
            $hostname = $config->get('DB_HOSTNAME');
            $port = $config->get('DB_PORT');
            $dbName = $config->get('DB_NAME');
            return new PDO(
                "{$adapter}:host={$hostname};dbname={$dbName};port={$port}",
                $config->get('DB_USERNAME'),
                $config->get('DB_PASSWORD'),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_PERSISTENT => true,
                    PDO::ATTR_EMULATE_PREPARES => true,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e){
            $this->logger->error('Internal Server Error:', ["Error" => $e->getMessage()]);
            die('Internal Server Error');
        }
    }
}