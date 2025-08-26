<?php

namespace App\Services\CroxxAI;

use App\Services\CroxxAI\CompetencyService;
use App\Services\CroxxAI\AssessmentService;
use App\Services\CroxxAI\CourseService;
use App\Services\CroxxAI\TemplateService;
use App\Services\CroxxAI\TextGenerationService;

/**
 * Main CroxxAI Service that provides access to all AI-powered features
 * This service acts as a facade to all specialized AI services
 */
class CroxxAIService
{
    protected $competencyService;
    protected $assessmentService;
    protected $courseService;
    protected $templateService;
    protected $textGenerationService;

    public function __construct()
    {
        $this->competencyService = new CompetencyService();
        $this->assessmentService = new AssessmentService();
        $this->courseService = new CourseService();
        $this->templateService = new TemplateService();
        $this->textGenerationService = new TextGenerationService();
    }

    // ============================================
    // COMPETENCY MAPPING METHODS
    // ============================================

    /**
     * Generate competency mapping for a specific job role
     */
    public function generateCompetencyMapping(string $jobRole, string $language = 'en'): array
    {
        return $this->competencyService->generateCompetencyMapping($jobRole, $language);
    }

    /**
     * Generate competencies by job title
     */
    public function generateCompetenciesByJobTitle(string $jobTitle, string $language = 'en'): array
    {
        return $this->competencyService->generateCompetenciesByJobTitle($jobTitle, $language);
    }

    // ============================================
    // ASSESSMENT METHODS
    // ============================================

    /**
     * Generate assessment questions
     */
    public function generateAssessmentQuestions(string $title, array $competencies, string $level, int $totalQuestions, string $language = 'en'): array
    {
        return $this->assessmentService->generateAssessmentQuestions($title, $competencies, $level, $totalQuestions, $language);
    }

    // ============================================
    // COURSE CONTENT METHODS
    // ============================================

    /**
     * Curate course lessons
     */
    public function curateCourseLessons(array $course, string $language = 'en'): array
    {
        return $this->courseService->curateCourseLessons($course, $language);
    }

    // ============================================
    // TEMPLATE GENERATION METHODS
    // ============================================

    /**
     * Generate department template
     */
    public function generateDepartmentTemplate(string $department): array
    {
        return $this->templateService->generateDepartmentTemplate($department);
    }

    /**
     * Generate department level template with KPIs
     */
    public function generateDepartmentLevelTemplate(string $department): array
    {
        return $this->templateService->generateDepartmentLevelTemplate($department);
    }

    // ============================================
    // TEXT GENERATION METHODS
    // ============================================

    /**
     * Generate text using completion endpoint (legacy)
     */
    public function generateText(string $prompt, string $model = 'gpt-3.5-turbo-instruct', int $maxTokens = 100): array
    {
        return $this->textGenerationService->generateText($prompt, $model, $maxTokens);
    }

    /**
     * Generate text using chat completions (recommended)
     */
    public function generateChatText(string $prompt, string $systemPrompt = "You are a helpful assistant.", string $model = null, float $temperature = null, int $maxTokens = null): string
    {
        return $this->textGenerationService->generateChatText($prompt, $systemPrompt, $model, $temperature, $maxTokens);
    }

    /**
     * Generate structured response (JSON, Markdown, HTML)
     */
    public function generateStructuredResponse(string $prompt, string $format = 'json', string $model = null, float $temperature = null): array
    {
        return $this->textGenerationService->generateStructuredResponse($prompt, $format, $model, $temperature);
    }

    /**
     * Generate text with custom instructions
     */
    public function generateWithInstructions(string $prompt, array $instructions = [], string $model = null, float $temperature = null): string
    {
        return $this->textGenerationService->generateWithInstructions($prompt, $instructions, $model, $temperature);
    }

    // ============================================
    // UTILITY METHODS
    // ============================================

    /**
     * Get all available services
     */
    public function getServices(): array
    {
        return [
            'competency' => $this->competencyService,
            'assessment' => $this->assessmentService,
            'course' => $this->courseService,
            'template' => $this->templateService,
            'text_generation' => $this->textGenerationService,
        ];
    }

    /**
     * Get a specific service instance
     */
    public function getService(string $serviceName)
    {
        $services = $this->getServices();
        
        if (!isset($services[$serviceName])) {
            throw new \InvalidArgumentException("Service '{$serviceName}' not found. Available services: " . implode(', ', array_keys($services)));
        }

        return $services[$serviceName];
    }

    /**
     * Check if a service is available
     */
    public function hasService(string $serviceName): bool
    {
        return array_key_exists($serviceName, $this->getServices());
    }
}