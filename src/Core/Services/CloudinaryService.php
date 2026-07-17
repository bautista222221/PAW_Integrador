<?php

namespace PAW\src\Core\Services;

use PAW\src\Core\Config;
use Monolog\Logger;

class CloudinaryService
{
    private ?string $cloudName;
    private ?string $uploadPreset;
    private Logger $logger;

    public function __construct(Config $config, Logger $logger)
    {
        $this->cloudName = $config->get('CLOUDINARY_CLOUD_NAME') ?? null;
        $this->uploadPreset = $config->get('CLOUDINARY_UPLOAD_PRESET') ?? null;
        $this->logger = $logger;
    }

    /**
     * Sube un archivo a Cloudinary usando la API HTTP cURL.
     * 
     * @param string $tmpPath Ruta temporal del archivo subido en el servidor.
     * @param string $originalName Nombre original del archivo (para extraer la extensión).
     * @return string|null La URL segura de acceso al recurso en Cloudinary, o null en caso de fallo.
     */
    public function subirArchivo(string $tmpPath, string $originalName): ?string
    {
        if (empty($this->cloudName) || empty($this->uploadPreset)) {
            $this->logger->warning("Subida a Cloudinary omitida: CLOUDINARY_CLOUD_NAME o CLOUDINARY_UPLOAD_PRESET no configurados en el .env.");
            return null;
        }

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Cloudinary maneja tres tipos principales de recursos:
        // - image: imágenes comunes (jpg, png, gif, webp, etc.)
        // - video: archivos de video y audio (mp4, webm, mp3, etc.)
        // - raw: cualquier otro archivo estático (zip, rar, pdf, docx, etc.)
        $resourceType = 'raw';
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'pdf'])) {
            $resourceType = 'image';
        } elseif (in_array($ext, ['mp4', 'avi', 'mov', 'mkv', 'webm', 'mp3', 'wav', 'ogg'])) {
            $resourceType = 'video';
        }

        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/{$resourceType}/upload";

        $postFields = [
            'file' => new \CURLFile($tmpPath, null, $originalName),
            'upload_preset' => $this->uploadPreset
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_TIMEOUT => 60, // Limite de 60 segundos de subida
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            $this->logger->error("Error cURL de conexión al subir archivo a Cloudinary: " . $err);
            return null;
        }

        $result = json_decode($response, true);
        if (isset($result['secure_url'])) {
            $this->logger->info("Archivo '{$originalName}' subido exitosamente a Cloudinary: " . $result['secure_url']);
            return $result['secure_url'];
        }

        $errorMsg = $result['error']['message'] ?? json_encode($result);
        $this->logger->error("Error retornado por la API de Cloudinary al subir '{$originalName}': " . $errorMsg);
        return null;
    }
}
