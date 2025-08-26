<?php

namespace App\Services\CroxxAI;

use Exception;

class TemplateService extends BaseCroxxAI
{
    protected $defaultModel = 'gpt-4-turbo';
    protected $defaultTemperature = 0.6;

    /**
     * Generate department template
     *
     * @param string $department
     * @return array
     * @throws Exception
     */
    public function generateDepartmentTemplate(string $department): array
    {
        try {
            $systemMessage = $this->createSystemMessage($this->getDepartmentTemplatePrompt());
            $userMessage = $this->createUserMessage($this->getDepartmentTemplateUserPrompt($department));

            $response = $this->makeChatRequest([$systemMessage, $userMessage], $this->defaultModel, $this->defaultTemperature);
            $templateContent = $this->extractJsonContent($response);

            $this->validateDepartmentTemplate($templateContent);

            return $templateContent;

        } catch (Exception $e) {
            $this->logAndThrow($e->getMessage(), 'generateDepartmentTemplate');
        }
    }

    /**
     * Generate department level template with KPIs
     *
     * @param string $department
     * @return array
     * @throws Exception
     */
    public function generateDepartmentLevelTemplate(string $department): array
    {
        try {
            $systemMessage = $this->createSystemMessage($this->getDepartmentLevelTemplatePrompt());
            $userMessage = $this->createUserMessage($this->getDepartmentLevelTemplateUserPrompt($department));

            $response = $this->makeChatRequest([$systemMessage, $userMessage], $this->defaultModel, $this->defaultTemperature);
            $templateContent = $this->extractJsonContent($response);

            $this->validateDepartmentLevelTemplate($templateContent);

            return $templateContent;

        } catch (Exception $e) {
            $this->logAndThrow($e->getMessage(), 'generateDepartmentLevelTemplate');
        }
    }

    /**
     * Validate department template structure
     *
     * @param array $template
     * @throws Exception
     */
    private function validateDepartmentTemplate(array $template): void
    {
        $requiredFields = ['department', 'department_goals', 'competency_mapping', 'recommended_assessments'];
        $this->validateRequiredFields($template, $requiredFields, 'department template');

        // Validate competency mapping structure
        if (!isset($template['competency_mapping']['technical_skills']) || 
            !isset($template['competency_mapping']['soft_skills'])) {
            throw new Exception('Invalid competency mapping structure');
        }
    }

    /**
     * Validate department level template structure
     *
     * @param array $template
     * @throws Exception
     */
    private function validateDepartmentLevelTemplate(array $template): void
    {
        $requiredFields = ['department', 'department_goals', 'competency_mapping', 'level_kpis'];
        $this->validateRequiredFields($template, $requiredFields, 'department level template');

        // Validate level KPIs structure
        if (!is_array($template['level_kpis'])) {
            throw new Exception('Level KPIs must be an array');
        }
    }

    /**
     * Get the system prompt for department template
     *
     * @return string
     */
    private function getDepartmentTemplatePrompt(): string
    {
        return 'You are an advance AI assistant tasked with creating department templates for competency mapping and assessment recommendations.

The competency mapping should include a comprehensive range of skills divided into technical and soft skills relevant.

For competency mapping:
- Generates competencies required for a job role or job title based on their level. These levels are divided into four groups: Beginner, Intermediate, Advance, and Expert.
- Provide a simple description that explains the competency
- Assign a reasonable default target score based on the level

For assessments:
- Recommend assessment types that effectively measure the competencies
- Ensure assessments are practical and implementable
- Include expected performance metrics
- Specify which competencies each assessment can measure

Provide a detailed JSON response with the following structure:
{
    "department": "<Department Name>",
    "department_goals": [
        {
            "goal_name": "<Goal Name>",
            "description": "<Goal Description>",
            "timeline": "<Timeline>"
        }
    ],
    "competency_mapping": {
        "technical_skills": [
            {
                "competency": "<Competency Name>",
                "description": "<Description>",
                "level": "<beginner|intermediate|advance|expert>",
                "target_score": <Default target score (1-100)>
            }
        ],
        "soft_skills": [
            {
                "competency": "<Competency Name>",
                "description": "<Description>",
                "level": "<beginner|intermediate|advance|expert>",
                "target_score": <Default target score (1-100)>
            }
        ]
    },
    "recommended_assessments": [
        {
            "name": "<Assessment Name>",
            "description": "<Assessment Description>",
            "suitable_levels": ["<level>", "<level>"],
            "expected_percentage": <Expected performance percentage (1-100)>,
            "applicable_competencies": ["<Competency Name>", "<Competency Name>"]
        }
    ]
}';
    }

    /**
     * Get the user prompt for department template
     *
     * @param string $department
     * @return string
     */
    private function getDepartmentTemplateUserPrompt(string $department): string
    {
        return "Generate a comprehensive competency mapping and assessment recommendations for the {$department} department. Focus on core competencies with appropriate levels and practical assessment methods.";
    }

    /**
     * Get the system prompt for department level template
     *
     * @return string
     */
    private function getDepartmentLevelTemplatePrompt(): string
    {
        return 'You are an advanced AI assistant tasked with creating department templates for competency mapping and KPI generation.

The competency mapping should include a wide range of skills divided into technical and soft skills relevant to the department. Each competency should include a description of its role and purpose in the department. Competencies should not only cover essential tasks but also growth opportunities.

Not all competencies in the mapping will be mandatory for KPIs. Only critical and measurable competencies should be included in the KPIs template.

For KPIs:
- Structure should define Beginner, Intermediate, Advance, and Expert levels.
- Each level must have unique KPIs with measurable targets and mandatory competencies.
- KPIs should be defined with clear names, descriptions, frequency, and weights to reflect their impact.

Provide a detailed JSON response with the following structure:
{
    "department": "<Department Name>",
    "department_goals": [
        {
            "goal_name": "<Goal Name>",
            "description": "<Goal Description>",
            "timeline": "<Timeline>"
        }
    ],
    "competency_mapping": [
        {
            "technical_skills": [
                {
                    "competency": "<Competency Name>",
                    "description": "<Description>",
                }
            ],
            "soft_skills": [
                {
                    "competency": "<Competency Name>",
                    "description": "<Description>",
                }
            ]
        }
    ],
    "level_kpis": [
        {
            "level": "<Beginner|Intermediate|Advance|Expert>",
            "kpis": [
                {
                    "kpi_name": "<KPI Name>",
                    "description": "<KPI Description>",
                    "frequency": "<Measurement Frequency>",
                    "mandatory_competencies": [
                        {
                            "competency": "<Competency Name from competency_mapping>",
                            "target_score": "<Target Score 0-100>",
                            "weight": "<Weight 0-5>"
                        }
                    ]
                }
            ]
        }
    ]
}';
    }

    /**
     * Get the user prompt for department level template
     *
     * @param string $department
     * @return string
     */
    private function getDepartmentLevelTemplateUserPrompt(string $department): string
    {
        return "Generate a detailed competency mapping and KPI template for the {$department} department. Include a broad range of competencies with clear descriptions, and ensure KPIs only use essential measurable skills.";
    }
}