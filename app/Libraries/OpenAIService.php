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

    public function generateAssessmentQuestion($competency)
    {
        $response = $this->client->post('chat/completions', [
            'json' => [
                'model' => 'gpt-3.5-turbo-1106',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant that generates quiz questions based on a competency. Respond with one short question and four plausible options/answers, of which only one is correct. Provide your answer in JSON structure like this {"topic": "<The topic of the quiz>", "question": "<The quiz question you generate>", "options": {"option1": {"body": "<Plausible option 1>", "isAnswer": <true or false>}, "option2": {"body": "<Plausible option 2>", "isAnswer": <true or false>}, "option3": {"body": "<Plausible option 3>", "isAnswer": <true or false>}, "option4": {"body": "<Plausible option 4>", "isAnswer": <true or false>}}}'
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Provide a question with four possible answers about: The Premier League',
                    ],
                    [
                        'role' => 'assistant',
                        'content' => '{"topic": "Premier League location", "question": "Where is the Premier League played?",  "options": {"option1": {"body": "France", "isAnswer": false}, "option2": {"body": "England", "isAnswer": true}, "option3": {"body": "Sweden", "isAnswer": false}, "option4": {"body": "Italy", "isAnswer": false}}}',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Provide a question with four possible answers about: {$competency}",
                    ],
                ],
            ],
        ]);

        $completion = json_decode($response->getBody()->getContents(), true);
        return json_decode($completion['choices'][0]['message']['content'], true);
    }

    public function generateCompetencyMapping($job_role)
    {
        // $prompt = "Generate a competency mapping for the Supply chain. Group it into a table which include Technical Skill and Soft Skill";

        $response = $this->client->post('chat/completions', [
            'json' => [
                'model' => 'gpt-3.5-turbo-1106',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant that generates competencies required for a job role or job title based on their level. These levels are divided into four groups namely beginner, intermediate, advance and expert. Respond by grouping these competencies into technical skills and soft skills based on the level. Provide a valid JSON format structure like this [{"job_role": "<Job Role>", "level": "<basic>"},{ "technical_skill": [{"competency": "<Competency Name>", "description": "Competency Description"} ]}, {"soft_skill": [{"competency": "<Competency Name>", "description": "Competency Description"}] }]',
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Generate a competency mapping for a Beginner level, Supply Chain',
                    ],
                    [
                        'role' => 'assistant',
                        'content' => '[{ "technical_skill": [ {"competency": "Demand Planning", "description": "Ability to forecast and plan for future demand based on historical data and market trends."}, {"competency": "Inventory Management", "description": "Proficiency in managing and optimizing inventory levels to ensure efficient supply chain operations.."} ]}, {  "soft_skill": [   {"competency": "Communication", "description": "Effective communication with internal teams, suppliers, and other stakeholders to ensure clear information flow."} ]  }]',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Generate a competency mapping for  {$job_role}",
                    ],
                ],
                'temperature' => 0.8,
            ],
        ]);

        $completion = json_decode($response->getBody()->getContents(), true);
        return json_decode($completion['choices'][0]['message']['content'], true);
    }
}
