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

            $this->validateCourseLessons($lessons);

            return $lessons;

        } catch (Exception $e) {
            $this->logAndThrow($e->getMessage(), 'curateCourseLessons');
        }
    }

    /**
     * Validate course lessons structure
     *
     * @param array $lessons
     * @throws Exception
     */
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
Create 4 lessons as JSON with fields: 
title, content, level, keywords. 
Content must be detailed but <= 3400 words.";
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