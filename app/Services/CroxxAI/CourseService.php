<?php

namespace App\Services\CroxxAI;

use Exception;

class CourseService extends BaseCroxxAI
{
    /**
     * Curate course lessons
     *
     * @param array $course
     * @param string $language
     * @return array
     * @throws Exception
     */
    public function curateCourseLessons(array $course, string $language = 'en'): array
    {
        try {
            $systemMessage = $this->createSystemMessage($this->getCourseLessonsPrompt($language));
            $userMessage = $this->createUserMessage($this->getCourseLessonsUserPrompt($course, $language));

            $response = $this->makeChatRequest(
                [$systemMessage, $userMessage], 
                null, 
                0.8  // Higher temperature for creative content
            );

            $lessons = $this->extractJsonContent($response);

            if (!is_array($lessons)) {
                throw new Exception('Invalid JSON format returned from API');
            }

            // info('Curated Lessons: ' . print_r($lessons, true));
            $this->validateCourseLessons($lessons);

            return $lessons;

        } catch (Exception $e) {
            $this->logAndThrow($e->getMessage(), 'curateCourseLessons');
        }
    }

    /**
     * Extract and parse course lessons from API response
     * Handles both pure JSON and markdown-wrapped JSON responses
     *
     * @param array $response
     * @return array
     * @throws Exception
     */
    private function extractAndParseCourseLessons(array $response): array
    {
        $rawContent = $response['choices'][0]['message']['content'];
        
        // Handle markdown code blocks - extract JSON from ```json blocks
        if (strpos($rawContent, '```json') !== false) {
            $lessons = [];
            
            // Extract all JSON blocks
            preg_match_all('/```json\s*(\{.*?\})\s*```/s', $rawContent, $matches);
            
            if (!empty($matches[1])) {
                foreach ($matches[1] as $jsonBlock) {
                    $lesson = json_decode($jsonBlock, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && is_array($lesson)) {
                        $lessons[] = $lesson;
                    }
                }
                
                return $lessons;
            }
        }
        
        // Try to parse as regular JSON array
        $lessons = json_decode($rawContent, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($lessons)) {
            return $lessons;
        }
        
        // If we still can't parse, try to extract JSON from the content
        // Remove any non-JSON content before and after
        $cleanContent = $this->cleanJsonContent($rawContent);
        $lessons = json_decode($cleanContent, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($lessons)) {
            return $lessons;
        }
        
        throw new Exception('Error parsing course lessons JSON: ' . json_last_error_msg());
    }

    /**
     * Clean content to extract valid JSON
     *
     * @param string $content
     * @return string
     */
    private function cleanJsonContent(string $content): string
    {
        // Remove markdown code blocks
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/\s*```/', '', $content);
        
        // Try to find JSON array or object patterns
        if (preg_match('/\[.*\]/s', $content, $matches)) {
            return trim($matches[0]);
        }
        
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            return '[' . trim($matches[0]) . ']'; // Wrap single object in array
        }
        
        return $content;
    }
    private function validateCourseLessons(array $lessons): void
    {
        $requiredFields = ['title', 'content', 'level', 'keywords'];

        foreach ($lessons as $lesson) {
            $this->validateRequiredFields($lesson, $requiredFields, 'lesson');
            
            // Additional validation for content length
            if (strlen($lesson['content']) > 3400 * 6) { // Accounting for multibyte characters
                throw new Exception("Lesson content exceeds maximum length limit");
            }
        }
    }

    /**
     * Get the system prompt for course lessons
     *
     * @param string $language
     * @return string
     */
    private function getCourseLessonsPrompt(string $language): string
    {
        return "You are an educational content creator. Generate lessons in {$language}. 
        Levels must remain in English: beginner, intermediate, advance, expert. 

        IMPORTANT: Respond with ONLY a valid JSON array. Do NOT use markdown code blocks or any other formatting.

        Create 5 lessons as a JSON array with the following structure:
        [
        {
            \"title\": \"Lesson title\",
            \"content\": \"Detailed lesson content\",
            \"level\": \"beginner|intermediate|advance|expert\",
            \"keywords\": [\"keyword1\", \"keyword2\", \"keyword3\"]
        }
        ]

        Content must be detailed but <= 3400 words per lesson.
        Return ONLY the JSON array, no explanations or markdown.";
    }

    /**
     * Get the user prompt for course lessons
     *
     * @param array $course
     * @param string $language
     * @return string
     */
    private function getCourseLessonsUserPrompt(array $course, string $language): string
    {
        return "Generate 4 lessons in {$language} for the following course: 
- Department: {$course['department']} 
- Title: {$course['title']} 
- Experience Level: {$course['level']}.";
    }
}