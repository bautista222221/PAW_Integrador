<?php

namespace PAW\src\Core;

use Dotenv\Dotenv;

class Config{
    private array $configs;
    public function __construct()
    {
        $envPath = __DIR__ . '/../../.env';
        
        if (file_exists($envPath)) {
            $dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../../');
            $dotenv->load();
        }

        $this->configs["LOG_LEVEL"] = getenv("LOG_LEVEL") ?? "debug";
        $path = getenv("LOG_PATH") ?? __DIR__ . "/logs/app.log";
        $this->configs["LOG_PATH"] = $this->joinPaths('..', $path);

        $this->configs["DB_ADAPTER"] = getenv("DB_ADAPTER") ?? "pgsql";
        $this->configs["DB_HOSTNAME"] = getenv("DB_HOSTNAME") ?? "localhost";
        $this->configs["DB_PORT"] = getenv("DB_PORT") ?? "5432";
        $this->configs["DB_NAME"] = getenv("DB_NAME") ?? "paw";
        $this->configs["DB_USERNAME"] = getenv("DB_USERNAME") ?? "admin";
        $this->configs["DB_PASSWORD"] = getenv("DB_PASSWORD") ?? "";
        $this->configs["DB_CHARSET"] = getenv("DB_CHARSET") ?? "utf8";
        $this->configs["IA_KEY"] = getenv("IA_KEY") ?? "your-ia-key";
        $this->configs["IA_PROVIDER"] = getenv("IA_PROVIDER") ?? "gemini";
        $this->configs["IA_MODEL"] = getenv("IA_MODEL") ?? "gemini-1.5-flash";
        $this->configs["IA_DEBUG"] = (getenv("IA_DEBUG") === 'true' || getenv("IA_DEBUG") === '1');
    }

    public function get(string $key): mixed
    {
        return $this->configs[$key] ?? null;
    }

    public function joinPaths(){
        $paths = array();
        foreach(func_get_args() as $arg){
            if($arg !== ''){
                $paths[] = $arg;
            }
        }
        return preg_replace('#/+#', '/', join('/', $paths));
    }
}