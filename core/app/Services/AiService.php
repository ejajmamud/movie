<?php

namespace App\Services;

class AiService
{
    /**
     * Generate metadata for a title using OpenRouter.
     *
     * @param string $title
     * @param string $type (movie or tv)
     * @return array|null
     */
    public static function generateMetadata($title, $type = 'movie')
    {
        $general = gs();
        $apiKey = @$general->openrouter_key ?: env('OPENROUTER_API_KEY');
        if (!$apiKey) {
            return null;
        }
        $model = @$general->ai_model ?: 'google/gemini-2.5-flash';

        $url = 'https://openrouter.ai/api/v1/chat/completions';
        $prompt = "Provide a high-quality synopsis (description), a catchy tagline, a comma-separated list of top 5 actors (casts), and a comma-separated list of directors (director) for the {$type}: \"{$title}\".\n";
        $prompt .= "Return the response ONLY as a valid JSON object matching this schema:\n";
        $prompt .= "{\n";
        $prompt .= "  \"description\": \"Detailed synopsis here\",\n";
        $prompt .= "  \"tagline\": \"Catchy tagline here\",\n";
        $prompt .= "  \"casts\": \"Actor 1, Actor 2, Actor 3, Actor 4, Actor 5\",\n";
        $prompt .= "  \"director\": \"Director Name\"\n";
        $prompt .= "}\n";
        $prompt .= "Do not include any markdown styling like ```json or any other text before or after the JSON.";

        $postData = [
            'model' => $model,
            'max_tokens' => 1000,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'HTTP-Referer: http://movie.test',
            'X-Title: PlayLab Movie Website'
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            curl_close($ch);
            return null;
        }

        curl_close($ch);
        $data = json_decode($response, true);

        if (isset($data['choices'][0]['message']['content'])) {
            $content = trim($data['choices'][0]['message']['content']);
            // Strip potential markdown wrappers just in case
            $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);
            
            $metadata = json_decode($content, true);
            if (is_array($metadata) && isset($metadata['description'])) {
                return $metadata;
            }
        }

        return null;
    }

    /**
     * Resolve the official IMDb ID (e.g. tt0944947) for a title.
     *
     * @param string $title
     * @param string $type (movie or tv)
     * @return string|null
     */
    public static function resolveImdbId($title, $type = 'movie')
    {
        $general = gs();
        $apiKey = @$general->openrouter_key ?: env('OPENROUTER_API_KEY');
        if (!$apiKey) {
            return null;
        }
        $model = @$general->ai_model ?: 'google/gemini-2.5-flash';

        $url = 'https://openrouter.ai/api/v1/chat/completions';
        $prompt = "Identify the official IMDb ID (tconst starting with 'tt') for the {$type} titled: \"{$title}\".\n";
        $prompt .= "Return the response ONLY as a JSON object with this exact key: \"imdb_id\". Example:\n";
        $prompt .= "{\n";
        $prompt .= "  \"imdb_id\": \"tt1234567\"\n";
        $prompt .= "}\n";
        $prompt .= "Do not include any markdown formatting like ```json, notes, or explanations.";

        $postData = [
            'model' => $model,
            'max_tokens' => 200,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'HTTP-Referer: http://movie.test',
            'X-Title: PlayLab Movie Website'
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            curl_close($ch);
            return null;
        }

        curl_close($ch);
        $data = json_decode($response, true);

        if (isset($data['choices'][0]['message']['content'])) {
            $content = trim($data['choices'][0]['message']['content']);
            $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);
            
            $resData = json_decode($content, true);
            if (is_array($resData) && isset($resData['imdb_id']) && preg_match('/^tt\d+/i', $resData['imdb_id'])) {
                return $resData['imdb_id'];
            }
        }

        return null;
    }

    /**
     * Log a message to storage/logs/ai_sync.log.
     *
     * @param string $message
     * @return void
     */
    public static function log($message)
    {
        $logPath = storage_path('logs/ai_sync.log');
        @file_put_contents($logPath, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n", FILE_APPEND);
    }
}
