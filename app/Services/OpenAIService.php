<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function generateText($prompt, $model = 'gpt-3.5-turbo-1106')
    {
        $response = $this->client->post('completions', [
            'json' => [
                'model' => $model,
                'prompt' => $prompt,
                'max_tokens' => 100,  // Adjust as needed
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }


    public function generateCompetencyMapping($job_role, $language = 'en')
{
    try {
        $response = $this->client->post('chat/completions', [
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a helpful assistant that generates competency mappings for job roles in multiple languages. 
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
]"
                    ],
                    [
                        'role' => 'user',
                        'content' => "Generate a competency mapping for the job role of {$job_role} at all levels. 
Respond in language code: {$language}. 
Remember: 'level' must always remain in English lowercase values (beginner, intermediate, advance, expert).",
                    ],
                ],
                'temperature' => 0.7,
            ]
        ]);

        $responseBody = $response->getBody()->getContents();

        // Decode the JSON response
        $content = json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error decoding JSON response from API: ' . json_last_error_msg());
        }

        // Extract and validate the content
        if (!isset($content['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid format: missing \"content\" in API response.');
        }

        $rawContent = $content['choices'][0]['message']['content'];
        $competencyMappings = json_decode($rawContent, true);

        if (!is_array($competencyMappings)) {
            throw new \Exception('Invalid format: Competency mappings should be an array.');
        }

        return $competencyMappings;

    } catch (\Exception $e) {
        Log::error("Error generating competency mapping: " . $e->getMessage());
        throw new \Exception("Error generating competency mapping: " . $e->getMessage());
    }
}


  public function generateAssessmentQuestion($title, $competencies, $level, $total_question, $language = 'en')
{
    try {
        $response = $this->client->post('chat/completions', [
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a helpful assistant that generates assessment questions in {$language}. 
                                      Levels must remain in English as: beginner, intermediate, advance, expert. 
                                      Generate exactly {$total_question} questions, each with four options and one correct answer. 
                                      Answer must be one of 'option1', 'option2', 'option3', 'option4'. 
                                      Structure: 
                                      [{\"competency_name\": \"<Competency>\", \"question\": \"<Question>\", \"level\": \"<Level>\", 
                                      \"option1\": \"<Option 1>\", \"option2\": \"<Option 2>\", \"option3\": \"<Option 3>\", \"option4\": \"<Option 4>\", \"answer\": \"<Option>\"}]"
                    ],
                    [
                        'role' => 'user',
                        'content' => "Generate {$total_question} assessment questions in {$language} for: 
                                      - Title: {$title} 
                                      - Competencies: " . implode(", ", $competencies) . " 
                                      - Level: {$level}. 
                                      Each must follow the given structure and keep level in English."
                    ],
                ],
            ],
        ]);

        $responseBody = $response->getBody()->getContents();
        $content = json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error decoding JSON response from API: ' . json_last_error_msg());
        }

        $rawContent = trim($content['choices'][0]['message']['content']);
        $questions = json_decode($rawContent, true);

        if (!is_array($questions)) {
            throw new \Exception("Invalid question format returned.");
        }

        foreach ($questions as $question) {
            if (!isset($question['competency_name'], $question['question'], $question['level'], 
                       $question['option1'], $question['option2'], $question['option3'], 
                       $question['option4'], $question['answer'])) {
                throw new \Exception("Missing required fields in question.");
            }

            if (!in_array($question['answer'], ['option1', 'option2', 'option3', 'option4'])) {
                throw new \Exception("Answer for question '{$question['question']}' is invalid.");
            }
        }

        return $questions;

    } catch (\Exception $e) {
        \Log::error("Error generating assessment questions: " . $e->getMessage());
        throw new \Exception("Error generating assessment questions: " . $e->getMessage());
    }
}

public function curateCourseLessons($course, $language = 'en') {
    try {
        $response = $this->client->post('chat/completions', [
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'temperature' => 0.8,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are an educational content creator. Generate lessons in {$language}. 
                                      Levels must remain in English: beginner, intermediate, advance, expert. 
                                      Create 4 lessons as JSON with fields: 
                                      title, content, level, keywords. 
                                      Content must be detailed but <= 3400 words."
                    ],
                    [
                        'role' => 'user',
                        'content' => "Generate 4 lessons in {$language} for the following course: 
                                      - Department: {$course['department']} 
                                      - Title: {$course['title']} 
                                      - Experience Level: {$course['level']}."
                    ],
                ],
            ],
        ]);

        $responseBody = json_decode($response->getBody()->getContents(), true);
        $lessons = json_decode($responseBody['choices'][0]['message']['content'], true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($lessons)) {
            throw new \Exception('Invalid JSON format returned from API');
        }

        return $lessons;

    } catch (\Exception $e) {
        \Log::error("Error generating course lessons: " . $e->getMessage());
        throw new \Exception("Error generating course lessons: " . $e->getMessage());
    }
}

public function generateCompetenciesByJobTitle($job_title, $language = 'en')
{
    try {
        $response = $this->client->post('chat/completions', [
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a helpful assistant that generates competencies in {$language}. 
                                      Competency names must remain standard in English. 
                                      Provide JSON: 
                                      [{\"competency\": \"<Name>\", \"match_percentage\": \"<%>\", \"benchmark\": \"<%>\", \"description\": \"<Description in {$language}>\"}]"
                    ],
                    [
                        'role' => 'user',
                        'content' => "Generate competencies for job title '{$job_title}' in {$language}, 
                                      keeping competency names in English but descriptions in {$language}."
                    ]
                ],
                'temperature' => 0.7,
            ]
        ]);

        $responseBody = $response->getBody()->getContents();
        $content = json_decode($responseBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error decoding JSON response: ' . json_last_error_msg());
        }

        $rawContent = $content['choices'][0]['message']['content'];
        $competencyMappings = json_decode($rawContent, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($competencyMappings)) {
            throw new \Exception('Error parsing competency mappings: ' . json_last_error_msg());
        }

        return $competencyMappings;

    } catch (\Exception $e) {
        \Log::error("Error generating competency mapping: " . $e->getMessage());
        throw new \Exception("Error generating competency mapping: " . $e->getMessage());
    }
}


    // Extension methods for other OpenAI functionalities can be added here
    
    public function generateDepartmentTemplate($department)
    {
        try {
            $system_prompt = '
            You are an advance AI assistant tasked with creating department templates for competency mapping and assessment recommendations.

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

            $response = $this->client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-4-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $system_prompt,
                        ],
                        [
                            'role' => 'user',
                            'content' => "Generate a comprehensive competency mapping and assessment recommendations for the {$department} department. Focus on core competencies with appropriate levels and practical assessment methods.",
                        ],
                    ],
                    'temperature' => 0.6,
                ]
            ]);

            $apiResponse = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error decoding API response: ' . json_last_error_msg());
            }

            $content = $apiResponse['choices'][0]['message']['content'] ?? null;
            if (!$content) {
                throw new Exception('Invalid API response format: missing content');
            }

            $templateContent = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error decoding template content: ' . json_last_error_msg());
            }

            return $templateContent;
        } catch (Exception $e) {
            Log::error('Error generating department template: ' . $e->getMessage());
            throw new Exception('Failed to generate department template: ' . $e->getMessage());
        }
    }

    public function generateDepartmentLevelTemplate($department)
    {
        try {
            $system_prompt = 'You are an advanced AI assistant tasked with creating department templates for competency mapping and KPI generation.

            The competency mapping should include a wide range of skills divided into technical and soft skills relevant to the department. Each competency should include a  description of its role and purpose in the department. Competencies should not only cover essential tasks but also growth opportunities.

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

            $response = $this->client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-4-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $system_prompt,
                        ],
                        [
                            'role' => 'user',
                            'content' => "Generate a detailed competency mapping and KPI template for the {$department} department. Include a broad range of competencies with clear descriptions, and ensure KPIs only use essential measurable skills.",
                        ],
                    ],
                    'temperature' => 0.6,
                    // 'max_tokens' => 2000, // Adjust based on required output length
                ]
            ]);

            $apiResponse = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error decoding API response: ' . json_last_error_msg());
            }

            $content = $apiResponse['choices'][0]['message']['content'] ?? null;
            if (!$content) {
                throw new Exception('Invalid API response format: missing content');
            }

            $templateContent = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error decoding template content: ' . json_last_error_msg());
            }

            return $templateContent;
        } catch (Exception $e) {
            Log::error('Error generating department template: ' . $e->getMessage());
            throw new Exception('Failed to generate department template');
        }
    }
}
