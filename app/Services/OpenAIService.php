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


    public function generateAssessmentQuestion($title, $competencies, $level, $total_question)
    {
        try {
            $response = $this->client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a helpful assistant that generates assessment questions based on given competencies and a specified level. Generate exactly ' . $total_question . ' questions, each with four possible answers and only one correct answer. The answer must be one of the options provided (i.e., between "option1", "option2", "option3", "option4"). Each question should include the level specified. Use the following format for each question: [{"competency_name": "<The competency name>", "question": "<The quiz question>", "level": "<Level>", "option1": "<Option 1>", "option2": "<Option 2>", "option3": "<Option 3>", "option4": "<Option 4>", "answer": "<Option>"}]. Ensure that the answer is one of the provided options.',
                        ],
                        [
                            'role' => 'user',
                            'content' => "Generate {$total_question} assessment questions with four possible answers based on the following details:
                            - Title: {$title}
                            - Competencies: " . implode(", ", $competencies) . "
                            - Level: {$level}
                            - Total Questions: {$total_question}. Each question should include the level {$level} and the answer must be one of the provided options (option1, option2, option3, option4).",
                        ],
                        // [
                        //     'role' => 'assistant',
                        //     'content' => 'Here are some examples of how you should format the questions:'
                        // ],
                        // [
                        //     'role' => 'assistant',
                        //     'content' => '[{"competency_name": "javascript", "question": "What is the purpose of the \'let\' keyword in JavaScript?", "level": "intermediate", "option1": "Defines a constant variable", "option2": "Declares a block-scoped variable", "option3": "Creates a global variable", "option4": "Defines a function", "answer": "option2"}]'
                        // ],
                        // [
                        //     'role' => 'assistant',
                        //     'content' => '[{"competency_name": "frontend", "question": "What does CSS stand for?", "level": "intermediate", "option1": "Cascading Style Selector", "option2": "Creative Style Sheet", "option3": "Customizable Styling System", "option4": "Cascading Style Sheets", "answer": "option4"}]'
                        // ],
                        // [
                        //     'role' => 'assistant',
                        //     'content' => '[{"competency_name": "javascript", "question": "What does the \'this\' keyword refer to in JavaScript?", "level": "intermediate", "option1": "Refers to the parent function", "option2": "Refers to the global object", "option3": "Refers to the current object or context", "option4": "Refers to a specific DOM element", "answer": "option3"}]'
                        // ],
                    ],
                ],
            ]);

            $responseBody = $response->getBody()->getContents();
            // \Log::info('API Response:', ['response' => $responseBody]);

            // Extract JSON from response string
            $content = json_decode($responseBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Error decoding JSON response from API: ' . json_last_error_msg());
            }

            $rawContent = $content['choices'][0]['message']['content'];

            // Remove unwanted text and parse JSON array
            $rawContent = trim($rawContent);
            if (strpos($rawContent, '[') === 0) {
                $questions = json_decode($rawContent, true);
            } else {
                // Handle case where the response contains multiple JSON objects
                $questions = [];
                $items = explode("\n\n", $rawContent);
                foreach ($items as $item) {
                    $parsed = json_decode(trim($item), true);
                    if (is_array($parsed)) {
                        $questions[] = $parsed;
                    }
                }
            }

            // Validate the structure of the questions array
            foreach ($questions as $question) {
                if (!isset($question['competency_name'], $question['question'], $question['level'], $question['option1'], $question['option2'], $question['option3'], $question['option4'], $question['answer'])) {
                    throw new \Exception("One or more required fields are missing in the question data.");
                }

                if (!in_array($question['answer'], ['option1', 'option2', 'option3', 'option4'])) {
                    throw new \Exception("Answer for question '{$question['question']}' is not one of the provided options.");
                }
            }

            return $questions;
            // if (is_array($questions) && count($questions) === $total_question) {
            // } else {
            //     throw new \Exception("Invalid format or number of questions returned from the API. Expected {$total_question} questions, received " . count($questions));
            // }
        } catch (\Exception $e) {
            // Handle the exception, log the error, etc.
            \Log::error("Error generating assessment questions: " . $e->getMessage());
            throw new \Exception("Error generating assessment questions: " . $e->getMessage());
        }
    }

    public function generateCompetencyMapping($job_role)
    {
        try {
            $response = $this->client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a helpful assistant that generates competencies required for a job role or job title based on their level. These levels are divided into four groups: Beginner, Intermediate, Advance, and Expert. Provide a competency mapping for each level, including both technical and soft skills. Structure the response in JSON format as follows: [{"job_role": "<Job Role>", "level": "<Beginner>", "technical_skill": [{"competency": "<Competency Name>", "level": "<Beginner>", "description": "Competency Description"}], "soft_skill": [{"competency": "<Competency Name>", "level": "<Beginner>", "description": "Competency Description"}]}}, {"job_role": "<Job Role>", "level": "<Intermediate>", "technical_skill": [{"competency": "<Competency Name>", "level": "<Intermediate>", "description": "Competency Description"}], "soft_skill": [{"competency": "<Competency Name>", "level": "<Intermediate>", "description": "Competency Description"}]}}, {"job_role": "<Job Role>", "level": "<advance>", "technical_skill": [{"competency": "<Competency Name>", "level": "<advance>", "description": "Competency Description"}], "soft_skill": [{"competency": "<Competency Name>", "level": "<advance>", "description": "Competency Description"}]}}, {"job_role": "<Job Role>", "level": "<Expert>", "technical_skill": [{"competency": "<Competency Name>", "level": "<Expert>", "description": "Competency Description"}], "soft_skill": [{"competency": "<Competency Name>", "level": "<Expert>", "description": "Competency Description"}]}]',
                        ],
                        [
                            'role' => 'user',
                            'content' => "Generate a competency mapping for the job role of {$job_role} at all levels (Beginner, Intermediate, advance, Expert). Include both technical skills and soft skills for each level.",
                        ],
                        [
                            'role' => 'user',
                            'content' => "Provide examples of competency mappings for each level, including technical and soft skills for the job role of {$job_role}.",
                        ],
                    ],
                    'temperature' => 0.8,
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
                throw new \Exception('Invalid format: missing "content" in API response.');
            }

            $rawContent = $content['choices'][0]['message']['content'];
            $competencyMappings = json_decode($rawContent, true);

            // Validate the competency mappings
            if (!is_array($competencyMappings)) {
                throw new \Exception('Invalid format: Competency mappings should be an array.');
            }

            return $competencyMappings;

            foreach ($competencyMappings as $mapping) {
                if (!isset($mapping['job_role'], $mapping['level'], $mapping['technical_skill'], $mapping['soft_skill'])) {
                    throw new \Exception('Missing required fields in competency mapping.');
                }

                if (!is_array($mapping['technical_skill']) || !is_array($mapping['soft_skill'])) {
                    throw new \Exception('Technical skills and soft skills should be arrays.');
                }

                // foreach ($mapping['technical_skill'] as $skill) {
                //     if (!isset($skill['competency'], $skill['description'])) {
                //         throw new \Exception('Missing fields in technical skill.');
                //     }
                // }

                // foreach ($mapping['soft_skill'] as $skill) {
                //     if (!isset($skill['competency'], $skill['description'])) {
                //         throw new \Exception('Missing fields in soft skill.');
                //     }
                // }
            }


        } catch (\Exception $e) {
            // Log the error and rethrow it
            Log::error("Error generating competency mapping: " . $e->getMessage());
            throw new \Exception("Error generating competency mapping: " . $e->getMessage());
        }
    }

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

    public function curateCourseLessons($course) {
        try {
            // Prepare the data for the API request
            $response = $this->client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'temperature' => 0.8,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an educational content creator tasked with generating detailed lessons based on the course title, department, and experience level. Generate 4 lessons, each structured in a JSON response with the following fields: "title", "content", "level", and "keywords". The output should be in the format: [{"title": "<Lesson Title>", "content": "<Lesson Content>", "level": "<Lesson Level>", "keywords": "[<2 - 3 Keywords>]"}]. Ensure that the "content" field provides comprehensive, detailed information that effectively teaches the lesson, and does not exceed 3400 words. The lesson’s competency should align with the lesson title.',
                        ],
                        [
                            'role' => 'user',
                            'content' => "Generate 4 lessons for the course with the following details:
                                          - Department: {$course['department']}
                                          - Title: {$course['title']}
                                          - Experience Level: {$course['level']}.",
                        ],
                    ],
                ],
            ]);

            // Parse the returned content from the assistant
            $responseBody = json_decode($response->getBody()->getContents(), true);

            // Check if the response is properly formatted as expected
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON format returned from API');
            }

            // Decode the lessons
            $lessons = json_decode($responseBody['choices'][0]['message']['content'], true);

            return $lessons;

        } catch (\Exception $e) {
            // Log the error and return an error response
            \Log::error("Error generating course lessons: " . $e->getMessage());
            throw new \Exception("Error generating course lessons: " . $e->getMessage());
        }
    }


    public function generateCompetenciesByJobTitle($job_title)
    {
        try {
            // Request OpenAI to generate competencies
            $response = $this->client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a helpful assistant that generates competencies required for a job title. Each competency must be a standard name, free from extra additions like examples or clarifications (e.g., "and" or "etc."). Any extra information or clarification should be placed in the description. The competency should also include a match percentage representing how related it is to the job title, a benchmark indicating the percentage required for assessment qualification, and a short description. Structure the response as a JSON array like this: [{"competency": "<Competency Name>", "match_percentage": "<Match %>", "benchmark": "<Benchmark %>", "description": "Short Description"}].',
                        ],
                        [
                            'role' => 'user',
                            'content' => "Generate competencies for the job title '{$job_title}'. Each competency must include a match percentage, a benchmark for assessment, and a short description. Ensure that the competency name is clear and standard, with any extra details moved to the description. Exclude levels.",
                        ]
                    ],
                    'temperature' => 0.7,
                ]
            ]);

            $responseBody = $response->getBody()->getContents();

            // Decode the JSON response from OpenAI
            $content = json_decode($responseBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Error decoding JSON response: ' . json_last_error_msg());
            }

            // Validate the content structure
            if (!isset($content['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid API response format.');
            }

            // Extract the raw content of the competency mapping
            $rawContent = $content['choices'][0]['message']['content'];

            // Parse the competency mappings as JSON
            $competencyMappings = json_decode($rawContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Error parsing competency mappings: ' . json_last_error_msg());
            }

            // Return the competency mappings
            return $competencyMappings;

        } catch (\Exception $e) {
            // Log the error and rethrow it
            \Log::error("Error generating competency mapping: " . $e->getMessage());
            throw new \Exception("Error generating competency mapping: " . $e->getMessage());
        }
    }
}
