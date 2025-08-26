<?php

namespace App\Services\CroxxAI;

use Exception;

class CompetencyService extends BaseCroxxAI
{
    /**
     * Generate competency mapping for a specific job role
     *
     * @param string $jobRole
     * @param string $language
     * @return array
     * @throws Exception
     */
    public function generateCompetencyMapping(string $jobRole, string $language = 'en'): array
    {
        try {
            $systemMessage = $this->createSystemMessage($this->getCompetencyMappingPrompt($language));
            $userMessage = $this->createUserMessage($this->getCompetencyMappingUserPrompt($jobRole, $language));

            $response = $this->makeChatRequest([$systemMessage, $userMessage]);
            $competencyMappings = $this->extractJsonContent($response);

            if (!is_array($competencyMappings)) {
                throw new Exception('Invalid format: Competency mappings should be an array.');
            }

            return $competencyMappings;

        } catch (Exception $e) {
            $this->logAndThrow($e->getMessage(), 'generateCompetencyMapping');
        }
    }

    /**
     * Generate competencies by job title
     *
     * @param string $jobTitle
     * @param string $language
     * @return array
     * @throws Exception
     */
    public function generateCompetenciesByJobTitle(string $jobTitle, string $language = 'en'): array
    {
        try {
            $systemMessage = $this->createSystemMessage($this->getCompetenciesByTitlePrompt($language));
            $userMessage = $this->createUserMessage($this->getCompetenciesByTitleUserPrompt($jobTitle, $language));

            $response = $this->makeChatRequest([$systemMessage, $userMessage]);
            $competencyMappings = $this->extractJsonContent($response);

            if (!is_array($competencyMappings)) {
                throw new Exception('Error parsing competency mappings: Invalid array format');
            }

            return $competencyMappings;

        } catch (Exception $e) {
            $this->logAndThrow($e->getMessage(), 'generateCompetenciesByJobTitle');
        }
    }

    /**
     * Get the system prompt for competency mapping
     *
     * @param string $language
     * @return string
     */
    private function getCompetencyMappingPrompt(string $language): string
    {
        return "You are a helpful assistant that generates competency mappings for job roles in multiple languages. 
            The response must always be **valid JSON only** (no explanations, no extra text). 

            Requirements:
            1. Always include four levels: beginner, intermediate, advance, expert. 
            - These level values must remain in lowercase English: 'beginner', 'intermediate', 'advance', 'expert' (do not translate them).
            2. Each level must contain both 'technical_skills' and 'soft_skills', each as an array with multiple competencies. 
            3. Each competency must include: 'competency' (string), 'level' (one of the fixed English values), 'description' (string, translated into the requested language), and 'target_score' (integer between 50–100).
            4. Translate only 'competency' and 'description' into the requested language.
            5. Ensure at least 3–5 competencies per skills category per level. 

            Expected JSON format:

            [
            {
                \"job_role\": \"<Job Role>\",
                \"level\": \"beginner\",
                \"technical_skills\": [
                {\"competency\": \"<Competency Name>\", \"level\": \"beginner\", \"description\": \"<Description>\", \"target_score\": 60}
                ],
                \"soft_skills\": [
                {\"competency\": \"<Competency Name>\", \"level\": \"beginner\", \"description\": \"<Description>\", \"target_score\": 60}
                ]
            },
            {
                \"job_role\": \"<Job Role>\",
                \"level\": \"intermediate\",
                ...
            },
            {
                \"job_role\": \"<Job Role>\",
                \"level\": \"advance\",
                ...
            },
            {
                \"job_role\": \"<Job Role>\",
                \"level\": \"expert\",
                ...
            }
            ]";
    }

    /**
     * Get the user prompt for competency mapping
     *
     * @param string $jobRole
     * @param string $language
     * @return string
     */
    private function getCompetencyMappingUserPrompt(string $jobRole, string $language): string
    {
        return "Generate a competency mapping for the job role of {$jobRole} at all levels. 
        Respond in language code: {$language}. 
        Remember: 'level' must always remain in English lowercase values (beginner, intermediate, advance, expert).";
    }

    /**
     * Get the system prompt for competencies by title
     *
     * @param string $language
     * @return string
     */
    private function getCompetenciesByTitlePrompt(string $language): string
    {
        return "You are a helpful assistant that generates competencies in {$language}. 
        Competency names must remain standard in English. 
        Provide JSON: 
        [{\"competency\": \"<Name>\", \"match_percentage\": \"<%>\", \"benchmark\": \"<%>\", \"description\": \"<Description in {$language}>\"}]";
    }

    /**
     * Get the user prompt for competencies by title
     *
     * @param string $jobTitle
     * @param string $language
     * @return string
     */
    private function getCompetenciesByTitleUserPrompt(string $jobTitle, string $language): string
    {
        return "Generate competencies for job title '{$jobTitle}' in {$language}, 
keeping competency names in English but descriptions in {$language}.";
    }
}