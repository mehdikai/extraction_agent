<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VisionExtractionService
{
    private string $apiKey;
    private string $model;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.nvidia.key');
        $this->model = config('services.vision_llm.model', 'moonshotai/kimi-k2.5');
        $this->apiUrl = config('services.vision_llm.url', 'https://integrate.api.nvidia.com/v1/chat/completions');
    }

    public function extract(string $base64Image, string $mimeType, string $prompt): array
    {
        $startTime = microtime(true);

        // NVIDIA attend l'image inline dans le texte au format <img src="data:..."/>
        $imageTag = "<img src=\"data:{$mimeType};base64,{$base64Image}\" />";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept'        => 'application/json',
        ])
        ->timeout(120)
        ->post($this->apiUrl, [
            'model'       => $this->model,
            'max_tokens'  => 2000,
            'temperature' => 0.6,
            'top_p'       => 0.95,
            'messages'    => [
                [
                    'role'    => 'user',
                    'content' => [
                        [
                            'type'      => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mimeType};base64,{$base64Image}",
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => $prompt,
                        ],
                    ],
                ],
            ],
            'chat_template_kwargs' => ['thinking' => false], 
        ]);

        $latencyMs = round((microtime(true) - $startTime) * 1000);

        if ($response->failed()) {
            Log::error('NVIDIA Vision API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('NVIDIA API request failed: ' . $response->status());
        }

        // Kimi K2.5 met la réponse dans 'reasoning' quand content est null
        $rawContent = $response->json('choices.0.message.content')
                ?? $response->json('choices.0.message.reasoning')
                ?? '';

        Log::info('NVIDIA raw response', ['content' => $rawContent]);

        // Extraire le JSON même si le modèle ajoute du texte autour
        $cleaned = trim($rawContent);

        // Supprimer les blocs ```json ... ```
        $cleaned = preg_replace('/^```(?:json)?\s*\n?/m', '', $cleaned);
        $cleaned = preg_replace('/\n?```\s*$/m', '', $cleaned);
        $cleaned = trim($cleaned);

        // Si toujours pas du JSON pur, extraire le premier objet JSON trouvé
        if (!str_starts_with($cleaned, '{')) {
            preg_match('/\{.*\}/s', $cleaned, $matches);
            $cleaned = $matches[0] ?? $cleaned;
        }

        $decoded = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Failed to parse NVIDIA response JSON', [
                'raw'   => $rawContent,
                'error' => json_last_error_msg(),
            ]);
            throw new \RuntimeException('Model returned invalid JSON: ' . json_last_error_msg());
        }

        $decoded['_meta'] = [
            'model'      => $this->model,
            'latency_ms' => $latencyMs,
            'provider'   => 'nvidia',
        ];

        return $decoded;
    }
}