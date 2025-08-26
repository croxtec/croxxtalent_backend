<?php

namespace App\Services\CroxxAI;

use Exception;

class TextGenerationService extends BaseCroxxAI
{
    /**
     * Generate text using completion endpoint (legacy method)
     *
     * @param string $prompt
     * @param string $model
     * @param int $maxTokens
     * @return array
     * @throws Exception
     */
    public function generateText(string $prompt, string $model = 'gpt-3.5-turbo-instruct', int $maxTokens = 100): array
    {
        try {
            return $this->makeCompletionRequest($prompt, $model, $maxTokens);
        } catch (Exception $e) {
            $this->logAndThrow($e->getMessage(), 'generateText');
        }
    }

    /**
     * Generate text using chat completions (recommended method)
     *
     * @param string $prompt
     * @param string $systemPrompt
     * @param string|null $model
     * @param float|null $temperature
     * @param int|null $maxTokens
     * @return string
     * @throws Exception
     */
    public function generateChatText(
        string $prompt, 
        string $systemPrompt = "You are a helpful assistant.", 
        string $model = null, 
        float $temperature = null,
        int $maxTokens = null
    ): string {
        try {
            $messages = [
                $this->createSystemMessage($systemPrompt),
                $this->createUserMessage($prompt)
            ];

            $additionalParams = [];
            if ($maxTokens !== null) {
                $additionalParams['max_tokens'] = $maxTokens;
            }

            $response = $this->makeChatRequest($messages, $model, $temperature, $additionalParams);
            
            return $response['choices'][0]['message']['content'];

        } catch (Exception $e) {
            $this->logAndThrow($e->getMessage(), 'generateChatText');
        }
    }

    /**
     * Generate structured response (useful for JSON or formatted outputs)
     *
     * @param string $prompt
     * @param string $format
     * @param string|null $model
     * @param float|null $temperature
     * @return array
     * @throws Exception
     */
    public function generateStructuredResponse(
        string $prompt, 
        string $format = 'json',
        string $model = null, 
        float $temperature = null
    ): array {
        try {
            $systemPrompt = $this->getStructuredResponsePrompt($format);
            $messages = [
                $this->createSystemMessage($systemPrompt),
                $this->createUserMessage($prompt)
            ];

            $response = $this->makeChatRequest($messages, $model, $temperature);
            
            if ($format === 'json') {
                return $this->extractJsonContent($response);
            }

            return $response;

        } catch (Exception $e) {
            $this->logAndThrow($e->getMessage(), 'generateStructuredResponse');
        }
    }

    /**
     * Get system prompt for structured responses
     *
     * @param string $format
     * @return string
     */
    private function getStructuredResponsePrompt(string $format): string
    {
        switch (strtolower($format)) {
            case 'json':
                return "You are a helpful assistant that responds only in valid JSON format. Do not include any explanations or additional text outside the JSON structure.";
            case 'markdown':
                return "You are a helpful assistant that responds in well-formatted Markdown. Use appropriate headers, lists, and formatting.";
            case 'html':
                return "You are a helpful assistant that responds in clean, semantic HTML format.";
            default:
                return "You are a helpful assistant that provides structured, well-formatted responses.";
        }
    }

    /**
     * Generate text with custom instructions
     *
     * @param string $prompt
     * @param array $instructions
     * @param string|null $model
     * @param float|null $temperature
     * @return string
     * @throws Exception
     */
    public function generateWithInstructions(
        string $prompt, 
        array $instructions = [],
        string $model = null, 
        float $temperature = null
    ): string {
        try {
            $systemPrompt = $this->buildInstructionsPrompt($instructions);
            return $this->generateChatText($prompt, $systemPrompt, $model, $temperature);

        } catch (Exception $e) {
            $this->logAndThrow($e->getMessage(), 'generateWithInstructions');
        }
    }

    /**
     * Build system prompt from instructions array
     *
     * @param array $instructions
     * @return string
     */
    private function buildInstructionsPrompt(array $instructions): string
    {
        $basePrompt = "You are a helpful assistant. Please follow these specific instructions:\n\n";
        
        foreach ($instructions as $key => $instruction) {
            if (is_numeric($key)) {
                $basePrompt .= "- {$instruction}\n";
            } else {
                $basePrompt .= "- {$key}: {$instruction}\n";
            }
        }

        return $basePrompt;
    }
}