<?php

namespace App\Services\CroxxAI;

use Exception;

class AssessmentService extends BaseCroxxAI
{
    /**
     * Generate assessment questions
     *
     * @param string $title
     * @param array $competencies
     * @param string $level
     * @param int $totalQuestions
     * @param string $language
     * @return array
     * @throws Exception
     */
    public function generateAssessmentQuestions(string $title, array $competencies, string $level, int $totalQuestions, string $language = 'en'): array
    {
        try {
            $systemMessage = $this->createSystemMessage($this->getAssessmentQuestionsPrompt($language, $totalQuestions));
            $userMessage = $this->createUserMessage($this->getAssessmentQuestionsUserPrompt($title, $competencies, $level, $totalQuestions, $language));

            $response = $this->makeChatRequest([$systemMessage, $userMessage]);
            $rawContent = trim($response['choices'][0]['message']['content']);
            $questions = json_decode($rawContent, true);

            if (!is_array($questions)) {
                throw new Exception("Invalid question format returned.");
            }

            $this->validateAssessmentQuestions($questions);

            return $questions;

        } catch (Exception $e) {
            $this->logAndThrow($e->getMessage(), 'generateAssessmentQuestions');
        }
    }

    /**
     * Validate assessment questions structure
     *
     * @param array $questions
     * @throws Exception
     */
    private function validateAssessmentQuestions(array $questions): void
    {
        $requiredFields = [
            'competency_name', 'question', 'level', 
            'option1', 'option2', 'option3', 'option4', 'answer'
        ];

        foreach ($questions as $question) {
            $this->validateRequiredFields($question, $requiredFields, 'question');

            if (!in_array($question['answer'], ['option1', 'option2', 'option3', 'option4'])) {
                throw new Exception("Answer for question '{$question['question']}' is invalid.");
            }
        }
    }

    /**
     * Get the system prompt for assessment questions
     *
     * @param string $language
     * @param int $totalQuestions
     * @return string
     */
    private function getAssessmentQuestionsPrompt(string $language, int $totalQuestions): string
    {
        return "You are a helpful assistant that generates assessment questions in {$language}. 
Levels must remain in English as: beginner, intermediate, advance, expert. 
Generate exactly {$totalQuestions} questions, each with four options and one correct answer. 
Answer must be one of 'option1', 'option2', 'option3', 'option4'. 
Structure: 
[{\"competency_name\": \"<Competency>\", \"question\": \"<Question>\", \"level\": \"<Level>\", 
\"option1\": \"<Option 1>\", \"option2\": \"<Option 2>\", \"option3\": \"<Option 3>\", \"option4\": \"<Option 4>\", \"answer\": \"<Option>\"}]";
    }

    /**
     * Get the user prompt for assessment questions
     *
     * @param string $title
     * @param array $competencies
     * @param string $level
     * @param int $totalQuestions
     * @param string $language
     * @return string
     */
    private function getAssessmentQuestionsUserPrompt(string $title, array $competencies, string $level, int $totalQuestions, string $language): string
    {
        return "Generate {$totalQuestions} assessment questions in {$language} for: 
- Title: {$title} 
- Competencies: " . implode(", ", $competencies) . " 
- Level: {$level}. 
Each must follow the given structure and keep level in English.";
    }
}