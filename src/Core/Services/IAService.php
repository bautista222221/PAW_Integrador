<?php

namespace PAW\src\Core\Services;

use PAW\src\Core\Config;
use PAW\src\Core\Traits\Loggable;

class IAService
{
    use Loggable;

    private Config $config;
    private ?string $provider;
    private ?string $model;
    private ?string $apiKey;
    private bool $debug;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->provider = $config->get('IA_PROVIDER') ?? 'gemini';
        $this->model = $config->get('IA_MODEL') ?? 'gemini-1.5-flash';
        $this->apiKey = $config->get('IA_KEY') ?? null;
        $this->debug = (bool)($config->get('IA_DEBUG') ?? true);
    }

    /**
     * Obtiene recomendaciones complementarias para un curso de forma agnóstica al proveedor.
     */
    public function obtenerRecomendaciones(string $titulo, string $descripcion, array $temario): array
    {
        $prompt = $this->construirPrompt($titulo, $descripcion, $temario);

        if ($this->debug && $this->logger) {
            $this->logger->info("IAService: Iniciando consulta con proveedor: {$this->provider}, modelo: {$this->model}");
        }

        $parsed = null;
        $startTime = microtime(true);

        switch (strtolower($this->provider)) {
            case 'openai':
                $parsed = $this->llamarOpenAI($prompt);
                break;
            case 'openrouter':
                $parsed = $this->llamarOpenRouter($prompt);
                break;
            case 'gemini':
            default:
                $parsed = $this->llamarGemini($prompt);
                break;
        }

        $duration = round(microtime(true) - $startTime, 3);
        if ($this->debug && $this->logger) {
            $this->logger->info("IAService: Consulta completada en {$duration} segundos.");
        }

        // Si falla la API elegida, devolvemos el fallback dinámico
        if (!$parsed || !is_array($parsed) || !isset($parsed["recomendaciones"])) {
            if ($this->logger) {
                $this->logger->warning("IAService: Falló la consulta con {$this->provider}. Activando generador de fallback local.");
            }
            $parsed = $this->generarRecomendacionesFallback($titulo, $descripcion, $temario);
            $parsed['fallback'] = true;
        } else {
            $parsed['fallback'] = false;
        }

        return $parsed;
    }

    private function construirPrompt(string $titulo, string $descripcion, array $temario): string
    {
        return "Actuá como un asistente pedagógico especializado en diseño de cursos.
        Tu tarea es analizar la siguiente información sobre un curso y sugerir contenidos complementarios que puedan enriquecerlo. Las recomendaciones deben ser recursos concretos como libros, artículos, videos, podcasts, sitios web, etc.
        Cada recomendación debe tener obligatoriamente:
        - un campo \"tipo\" (libro, video, artículo, podcast, sitio web),
        - un campo \"titulo\",
        - y un campo \"descripcion\" (breve).

        ⚠️ Respondé únicamente con JSON válido, sin explicaciones ni texto adicional antes o después.
        Formato exacto de respuesta:
        {
            \"recomendaciones\": [
                {
                    \"tipo\": \"libro\" | \"video\" | \"artículo\" | \"podcast\" | \"sitio web\",
                    \"titulo\": \"Título del recurso\",
                    \"descripcion\": \"Breve descripción del recurso\"
                }
            ]
        }

        Datos del curso:
        Título: \"$titulo\"
        Descripción: \"$descripcion\"
        Temario: " . implode(", ", $temario);
    }

    /**
     * Integración nativa con Google Gemini
     */
    private function llamarGemini(string $prompt): ?array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";
        
        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "responseMimeType" => "application/json"
            ]
        ];

        $headers = ["Content-Type: application/json"];
        $response = $this->ejecutarCurl($url, $payload, $headers);

        if (!$response) return null;

        $json = json_decode($response, true);
        $content = $json["candidates"][0]["content"]["parts"][0]["text"] ?? null;

        if ($content) {
            return json_decode(trim($content), true);
        }

        return null;
    }

    /**
     * Integración con OpenAI
     */
    private function llamarOpenAI(string $prompt): ?array
    {
        $url = "https://api.openai.com/v1/chat/completions";

        $payload = [
            "model" => $this->model,
            "messages" => [
                ["role" => "user", "content" => $prompt]
            ],
            "response_format" => ["type" => "json_object"]
        ];

        $headers = [
            "Authorization: Bearer {$this->apiKey}",
            "Content-Type: application/json"
        ];

        $response = $this->ejecutarCurl($url, $payload, $headers);

        if (!$response) return null;

        $json = json_decode($response, true);
        $content = $json["choices"][0]["message"]["content"] ?? null;

        if ($content) {
            return json_decode(trim($content), true);
        }

        return null;
    }

    /**
     * Integración con OpenRouter
     */
    private function llamarOpenRouter(string $prompt): ?array
    {
        $url = "https://openrouter.ai/api/v1/chat/completions";

        $payload = [
            "model" => $this->model,
            "messages" => [
                ["role" => "user", "content" => $prompt]
            ],
            "response_format" => ["type" => "json_object"]
        ];

        $headers = [
            "Authorization: Bearer {$this->apiKey}",
            "Content-Type: application/json",
            "HTTP-Referer: http://localhost",
            "X-Title: Plataforma-PAD"
        ];

        $response = $this->ejecutarCurl($url, $payload, $headers);

        if (!$response) return null;

        $json = json_decode($response, true);
        $content = $json["choices"][0]["message"]["content"] ?? null;

        if ($content) {
            return json_decode(trim($content), true);
        }

        return null;
    }

    /**
     * Ejecuta cURL e imprime/loguea detalles de depuración precisos si IA_DEBUG es true
     */
    private function ejecutarCurl(string $url, array $payload, array $headers): ?string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // Debug de Headers Enviados
        if ($this->debug && $this->logger) {
            // Ocultamos la API Key en el log por seguridad
            $safeUrl = preg_replace('/key=[^&]+/', 'key=OCULTADA_KEY', $url);
            $safeHeaders = array_map(function($h) {
                if (stripos($h, 'Authorization:') === 0) return 'Authorization: Bearer OCULTADA_KEY';
                return $h;
            }, $headers);

            $this->logger->info("IAService [DEBUG REQUEST]:", [
                "URL" => $safeUrl,
                "Headers" => $safeHeaders,
                "Payload" => $payload
            ]);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($this->debug && $this->logger) {
            $this->logger->info("IAService [DEBUG RESPONSE]:", [
                "HTTP_CODE" => $httpCode,
                "Error_cURL" => $error,
                "Response_Raw" => $response ? json_decode($response, true) : null
            ]);
        }

        if ($httpCode !== 200) {
            return null;
        }

        return $response;
    }

    private function generarRecomendacionesFallback(string $titulo, string $descripcion, array $temario): array
    {
        $recomendaciones = [];
        $textoAnalizar = strtolower($titulo . " " . $descripcion . " " . implode(" ", $temario));

        if (strpos($textoAnalizar, 'php') !== false) {
            $recomendaciones = [
                [
                    "tipo" => "sitio web",
                    "titulo" => "PHP: The Right Way",
                    "descripcion" => "Buenas prácticas y estándares del ecosistema PHP moderno."
                ],
                [
                    "tipo" => "libro",
                    "titulo" => "Modern PHP de Josh Lockhart",
                    "descripcion" => "Arquitectura limpia y desarrollo profesional en PHP."
                ]
            ];
        } elseif (strpos($textoAnalizar, 'javascript') !== false || strpos($textoAnalizar, 'js') !== false) {
            $recomendaciones = [
                [
                    "tipo" => "libro",
                    "titulo" => "You Don't Know JS Yet",
                    "descripcion" => "Guía profunda del funcionamiento de JavaScript."
                ],
                [
                    "tipo" => "sitio web",
                    "titulo" => "MDN Web Docs",
                    "descripcion" => "Documentación de referencia del desarrollo frontend."
                ]
            ];
        } else {
            $recomendaciones = [
                [
                    "tipo" => "libro",
                    "titulo" => "Clean Code de Robert C. Martin",
                    "descripcion" => "Lectura elemental de buenas prácticas de programación."
                ],
                [
                    "tipo" => "sitio web",
                    "titulo" => "Roadmap.sh",
                    "descripcion" => "Rutas de aprendizaje para desarrolladores."
                ]
            ];
        }

        foreach ($temario as $index => $tema) {
            if ($index > 1) break;
            $recomendaciones[] = [
                "tipo" => "artículo",
                "titulo" => "Introducción teórica a: " . htmlspecialchars($tema),
                "descripcion" => "Artículo introductorio para guiar el estudio de este tema."
            ];
        }

        return ["recomendaciones" => $recomendaciones];
    }
}
