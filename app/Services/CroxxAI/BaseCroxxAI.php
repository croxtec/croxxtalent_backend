<?php

namespace App\Services\CroxxAI;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

abstract class BaseCroxxAI
{
    protected $client;
    protected $defaultModel;
    protected $defaultTemperature;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
        ]);
        
        $this->defaultModel = 'gpt-3.5-turbo';
        $this->defaultTemperature = 0.7;
    }

    /**
     * Make a chat completion request to OpenAI API
     *
     * @param array $messages
     * @param string|null $model
     * @param float|null $temperature
     * @param array $additionalParams
     * @return array
     * @throws Exception
     */
    protected function makeChatRequest(array $messages, string $model = null, float $temperature = null, array $additionalParams = []): array
    {
        try {
            $requestData = array_merge([
                'model' => $model ?? $this->defaultModel,
                'messages' => $messages,
                'temperature' => $temperature ?? $this->defaultTemperature,
            ], $additionalParams);

            $response = $this->client->post('chat/completions', [
                'json' => $requestData
            ]);

            $responseBody = $response->getBody()->getContents();
            $content = json_decode($responseBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error decoding JSON response from API: ' . json_last_error_msg());
            }

            if (!isset($content['choices'][0]['message']['content'])) {
                throw new Exception('Invalid format: missing "content" in API response.');
            }

            return $content;

        } catch (Exception $e) {
            Log::error("Error making chat request: " . $e->getMessage());
            throw new Exception("Error making chat request: " . $e->getMessage());
        }
    }

    /**
     * Make a completion request to OpenAI API (for older models)
     *
     * @param string $prompt
     * @param string $model
     * @param int $maxTokens
     * @return array
     * @throws Exception
     */
    protected function makeCompletionRequest(string $prompt, string $model = 'gpt-3.5-turbo-instruct', int $maxTokens = 100): array
    {
        try {
            $response = $this->client->post('completions', [
                'json' => [
                    'model' => $model,
                    'prompt' => $prompt,
                    'max_tokens' => $maxTokens,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (Exception $e) {
            Log::error("Error making completion request: " . $e->getMessage());
            throw new Exception("Error making completion request: " . $e->getMessage());
        }
    }

    /**
     * Extract and decode JSON content from API response
     *
     * @param array $response
     * @return array
     * @throws Exception
     */
    protected function extractJsonContent(array $response): array
    {
        $rawContent = $response['choices'][0]['message']['content'];
        $decodedContent = json_decode($rawContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error parsing JSON content: ' . json_last_error_msg());
        }

        return $decodedContent;
    }

    /**
     * Validate required fields in an array
     *
     * @param array $data
     * @param array $requiredFields
     * @param string $context
     * @throws Exception
     */
    protected function validateRequiredFields(array $data, array $requiredFields, string $context = 'data'): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Missing required field '{$field}' in {$context}");
            }
        }
    }

    /**
     * Create system message for consistent prompt formatting
     *
     * @param string $content
     * @return array
     */
    protected function createSystemMessage(string $content): array
    {
        return [
            'role' => 'system',
            'content' => $content
        ];
    }

    /**
     * Create user message for consistent prompt formatting
     *
     * @param string $content
     * @return array
     */
    protected function createUserMessage(string $content): array
    {
        return [
            'role' => 'user',
            'content' => $content
        ];
    }

    /**
     * Log error and throw exception with context
     *
     * @param string $message
     * @param string $context
     * @throws Exception
     */
    protected function logAndThrow(string $message, string $context): void
    {
        $fullMessage = "Error in {$context}: {$message}";
        Log::error($fullMessage);
        throw new Exception($fullMessage);
    }
}