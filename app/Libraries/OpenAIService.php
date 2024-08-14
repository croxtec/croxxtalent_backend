<?php

namespace App\Libraries;

use GuzzleHttp\Client;

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
            \Log::info('API Response:', ['response' => $responseBody]);

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

//     public function generateCompetencyMapping($job_role)
//     {
//         $response = $this->client->post('chat/completions', [
//             'json' => [
//                 'model' => 'gpt-3.5-turbo', // Use the standard GPT-3.5-turbo model
//                 'messages' => [
//                     [
//                         'role' => 'system',
//                         'content' => 'You are a helpful assistant that generates competencies required for a job role or job title based on their level. These levels are divided into four groups: Beginner, Intermediate, Advanced, and Expert. Provide a competency mapping for each level, including both technical and soft skills. Structure the response in JSON format for each level as follows: [{"job_role": "<Job Role>", "level": "<Beginner>", "technical_skills": [{"competency": "<Competency Name>", "description": "Competency Description"}], "soft_skills": [{"competency": "<Competency Name>", "description": "Competency Description"}]}}, {"job_role": "<Job Role>", "level": "<Intermediate>", "technical_skills": [{"competency": "<Competency Name>", "description": "Competency Description"}], "soft_skills": [{"competency": "<Competency Name>", "description": "Competency Description"}]}}, {"job_role": "<Job Role>", "level": "<Advanced>", "technical_skills": [{"competency": "<Competency Name>", "description": "Competency Description"}], "soft_skills": [{"competency": "<Competency Name>", "description": "Competency Description"}]}}, {"job_role": "<Job Role>", "level": "<Expert>", "technical_skills": [{"competency": "<Competency Name>", "description": "Competency Description"}], "soft_skills": [{"competency": "<Competency Name>", "description": "Competency Description"}]}]',
//                     ],
//                     [
//                         'role' => 'user',
//                         'content' => "Generate a competency mapping for the job role of Supply Chain at all levels (Beginner, Intermediate, Advanced, Expert). Include both technical skills and soft skills for each level.",
//                     ],
//                     [
//                         'role' => 'user',
//                         'content' => "Provide examples of competency mappings for each level, including technical and soft skills for the job role of Supply Chain.",
//                     ],
//                 ],
//                 'temperature' => 0.8,
//             ]
//         ]);

//         $completion = json_decode($response->getBody()->getContents(), true);
//         return json_decode($completion['choices'][0]['message']['content'], true);
//     }
// }

    public function generateCompetencyMapping($job_role)
    {
        try {
            $response = $this->client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a helpful assistant that generates competencies required for a job role or job title based on their level. These levels are divided into four groups: Beginner, Intermediate, Advanced, and Expert. Provide a competency mapping for each level, including both technical and soft skills. Structure the response in JSON format as follows: [{"job_role": "<Job Role>", "level": "<Beginner>", "technical_skills": [{"competency": "<Competency Name>", "level": "<Beginner>", "description": "Competency Description"}], "soft_skills": [{"competency": "<Competency Name>", "level": "<Beginner>", "description": "Competency Description"}]}}, {"job_role": "<Job Role>", "level": "<Intermediate>", "technical_skills": [{"competency": "<Competency Name>", "level": "<Intermediate>", "description": "Competency Description"}], "soft_skills": [{"competency": "<Competency Name>", "level": "<Intermediate>", "description": "Competency Description"}]}}, {"job_role": "<Job Role>", "level": "<Advanced>", "technical_skills": [{"competency": "<Competency Name>", "level": "<Advanced>", "description": "Competency Description"}], "soft_skills": [{"competency": "<Competency Name>", "level": "<Advanced>", "description": "Competency Description"}]}}, {"job_role": "<Job Role>", "level": "<Expert>", "technical_skills": [{"competency": "<Competency Name>", "level": "<Expert>", "description": "Competency Description"}], "soft_skills": [{"competency": "<Competency Name>", "level": "<Expert>", "description": "Competency Description"}]}]',
                        ],
                        [
                            'role' => 'user',
                            'content' => "Generate a competency mapping for the job role of {$job_role} at all levels (Beginner, Intermediate, Advanced, Expert). Include both technical skills and soft skills for each level.",
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

            info($competencyMappings);
            // Validate the competency mappings
            if (!is_array($competencyMappings)) {
                throw new \Exception('Invalid format: Competency mappings should be an array.');
            }

            return $competencyMappings;

            foreach ($competencyMappings as $mapping) {
                if (!isset($mapping['job_role'], $mapping['level'], $mapping['technical_skills'], $mapping['soft_skills'])) {
                    throw new \Exception('Missing required fields in competency mapping.');
                }

                if (!is_array($mapping['technical_skills']) || !is_array($mapping['soft_skills'])) {
                    throw new \Exception('Technical skills and soft skills should be arrays.');
                }

                // foreach ($mapping['technical_skills'] as $skill) {
                //     if (!isset($skill['competency'], $skill['description'])) {
                //         throw new \Exception('Missing fields in technical skill.');
                //     }
                // }

                // foreach ($mapping['soft_skills'] as $skill) {
                //     if (!isset($skill['competency'], $skill['description'])) {
                //         throw new \Exception('Missing fields in soft skill.');
                //     }
                // }
            }


        } catch (\Exception $e) {
            // Log the error and rethrow it
            \Log::error("Error generating competency mapping: " . $e->getMessage());
            throw new \Exception("Error generating competency mapping: " . $e->getMessage());
        }
        }
    }
