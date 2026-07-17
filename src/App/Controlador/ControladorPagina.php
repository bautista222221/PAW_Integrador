<?php

namespace PAW\src\App\Controlador;

use PAW\src\Core\Controlador;

class ControladorPagina extends Controlador
{
    public function index()
    {
        $titulo = "PAD - Inicio";
        require $this->viewsDir . 'index.view.php';
    }

    public function faq()
    {
        $titulo = "PAD - FAQ";
        require $this->viewsDir . 'faq.view.php';
    }

    public function soporte()
    {
        $titulo = "PAD - Soporte Técnico";
        $enviado = isset($_GET['enviado']) && $_GET['enviado'] == 1;
        require $this->viewsDir . 'soporte.view.php';
    }

    public function procesarSoporte()
    {
        global $log, $request;
        
        $nombre = $request->get('nombre');
        $email = $request->get('email');
        $asunto = $request->get('asunto');
        $mensaje = $request->get('mensaje');

        // 1. Registrar en el archivo de log general de la app usando Monolog
        if ($log) {
            $log->info("Ticket de soporte recibido", [
                'nombre' => $nombre,
                'email' => $email,
                'asunto' => $asunto,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'desconocida'
            ]);
        }

        // 2. Guardar el ticket en un archivo dedicado y legible
        $ticketFile = __DIR__ . '/../../../log/soporte_tickets.log';
        $fecha = date('Y-m-d H:i:s');
        
        $ticketData = "==================================================\n";
        $ticketData .= "FECHA: {$fecha}\n";
        $ticketData .= "NOMBRE: {$nombre}\n";
        $ticketData .= "EMAIL: {$email}\n";
        $ticketData .= "ASUNTO: {$asunto}\n";
        $ticketData .= "MENSAJE:\n{$mensaje}\n";
        $ticketData .= "==================================================\n\n";

        file_put_contents($ticketFile, $ticketData, FILE_APPEND);

        header("Location: /soporte?enviado=1");
        exit;
    }
}