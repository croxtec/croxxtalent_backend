<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EvaluationQuestionBank;

class EvaluationQuestionBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $questions = [
            // Communication
            [
                'competency_name' => 'Communication',
                'question' => 'What is the most important aspect of effective communication?',
                'option1' => 'Listening',
                'option2' => 'Speaking',
                'option3' => 'Writing',
                'option4' => 'Reading',
                'level' => 'beginner',
                'answer' => 'option1',
            ],
            [
                'competency_name' => 'Communication',
                'question' => 'Which of these is a barrier to effective communication?',
                'option1' => 'Feedback',
                'option2' => 'Noise',
                'option3' => 'Clarity',
                'option4' => 'Listening',
                'level' => 'intermediate',
                'answer' => 'option2',
            ],
            // Teamwork
            [
                'competency_name' => 'Teamwork',
                'question' => 'Which of the following is essential for effective teamwork?',
                'option1' => 'Conflict',
                'option2' => 'Collaboration',
                'option3' => 'Independence',
                'option4' => 'Competition',
                'level' => 'beginner',
                'answer' => 'option2',
            ],
            [
                'competency_name' => 'Teamwork',
                'question' => 'What is a key benefit of teamwork?',
                'option1' => 'Increased conflict',
                'option2' => 'Redundancy',
                'option3' => 'Synergy',
                'option4' => 'Isolation',
                'level' => 'intermediate',
                'answer' => 'option3',
            ],
            // Leadership
            [
                'competency_name' => 'Leadership',
                'question' => 'Which style of leadership involves making decisions without consulting others?',
                'option1' => 'Democratic',
                'option2' => 'Autocratic',
                'option3' => 'Laissez-faire',
                'option4' => 'Transformational',
                'level' => 'advance',
                'answer' => 'option2',
            ],
            [
                'competency_name' => 'Leadership',
                'question' => 'What is a key trait of a transformational leader?',
                'option1' => 'Micromanagement',
                'option2' => 'Vision',
                'option3' => 'Task-oriented',
                'option4' => 'Passivity',
                'level' => 'advance',
                'answer' => 'option2',
            ],
            // Problem Solving
            [
                'competency_name' => 'Problem Solving',
                'question' => 'What is the first step in problem-solving?',
                'option1' => 'Implementation',
                'option2' => 'Evaluation',
                'option3' => 'Identifying the problem',
                'option4' => 'Brainstorming solutions',
                'level' => 'intermediate',
                'answer' => 'option3',
            ],
            [
                'competency_name' => 'Problem Solving',
                'question' => 'Which of these is a problem-solving technique?',
                'option1' => 'SWOT Analysis',
                'option2' => 'Meditation',
                'option3' => 'Exercise',
                'option4' => 'Reading',
                'level' => 'intermediate',
                'answer' => 'option1',
            ],
            // General Knowledge
            [
                'competency_name' => 'General Knowledge',
                'question' => 'What is the capital of France?',
                'option1' => 'Berlin',
                'option2' => 'Madrid',
                'option3' => 'Paris',
                'option4' => 'Rome',
                'level' => 'intermediate',
                'answer' => 'option3',
            ],
            [
                'competency_name' => 'General Knowledge',
                'question' => 'Which planet is known as the Red Planet?',
                'option1' => 'Earth',
                'option2' => 'Mars',
                'option3' => 'Jupiter',
                'option4' => 'Saturn',
                'level' => 'intermediate',
                'answer' => 'option2',
            ],
            [
                'competency_name' => 'General Knowledge',
                'question' => 'What is the largest ocean on Earth?',
                'option1' => 'Atlantic Ocean',
                'option2' => 'Indian Ocean',
                'option3' => 'Arctic Ocean',
                'option4' => 'Pacific Ocean',
                'level' => 'intermediate',
                'answer' => 'option4',
            ],
            [
                'competency_name' => 'General Knowledge',
                'question' => 'Who wrote "Hamlet"?',
                'option1' => 'Charles Dickens',
                'option2' => 'Mark Twain',
                'option3' => 'William Shakespeare',
                'option4' => 'Leo Tolstoy',
                'level' => 'intermediate',
                'answer' => 'option3',
            ],
            [
                'competency_name' => 'General Knowledge',
                'question' => 'Which element has the chemical symbol O?',
                'option1' => 'Gold',
                'option2' => 'Oxygen',
                'option3' => 'Silver',
                'option4' => 'Iron',
                'level' => 'intermediate',
                'answer' => 'option2',
            ],
            // Technical Skills
            [
                'competency_name' => 'Technical Skills',
                'question' => 'What does HTML stand for?',
                'option1' => 'HyperText Markup Language',
                'option2' => 'Hyperlinks and Text Markup Language',
                'option3' => 'Home Tool Markup Language',
                'option4' => 'Hyperlinking Text Marking Language',
                'level' => 'intermediate',
                'answer' => 'option1',
            ],
            [
                'competency_name' => 'Technical Skills',
                'question' => 'Which programming language is known as the language of the web?',
                'option1' => 'Python',
                'option2' => 'Java',
                'option3' => 'JavaScript',
                'option4' => 'C++',
                'level' => 'intermediate',
                'answer' => 'option3',
            ],
            [
                'competency_name' => 'Technical Skills',
                'question' => 'In databases, what does SQL stand for?',
                'option1' => 'Structured Query Language',
                'option2' => 'Simple Query Language',
                'option3' => 'Standard Query Language',
                'option4' => 'Structured Question Language',
                'level' => 'intermediate',
                'answer' => 'option1',
            ],
            [
                'competency_name' => 'Technical Skills',
                'question' => 'What is the primary function of an operating system?',
                'option1' => 'To manage computer hardware and software resources',
                'option2' => 'To serve as a word processor',
                'option3' => 'To browse the internet',
                'option4' => 'To edit videos',
                'level' => 'intermediate',
                'answer' => 'option1',
            ],
            // Time Management
            [
                'competency_name' => 'Time Management',
                'question' => 'Which of the following is a technique for effective time management?',
                'option1' => 'Procrastination',
                'option2' => 'Multitasking',
                'option3' => 'Setting priorities',
                'option4' => 'Daydreaming',
                'level' => 'intermediate',
                'answer' => 'option3',
            ],
            [
                'competency_name' => 'Time Management',
                'question' => 'What is a common tool used in time management?',
                'option1' => 'Calendar',
                'option2' => 'Clock',
                'option3' => 'Calculator',
                'option4' => 'Phone',
                'level' => 'advance',
                'answer' => 'option1',
            ],
            // Conflict Resolution
            [
                'competency_name' => 'Conflict Resolution',
                'question' => 'What is the first step in conflict resolution?',
                'option1' => 'Ignoring the problem',
                'option2' => 'Identifying the conflict',
                'option3' => 'Taking sides',
                'option4' => 'Blaming others',
                'level' => 'intermediate',
                'answer' => 'option2',
            ],
            [
                'competency_name' => 'Conflict Resolution',
                'question' => 'Which of the following is a conflict resolution strategy?',
                'option1' => 'Avoidance',
                'option2' => 'Competition',
                'option3' => 'Collaboration',
                'option4' => 'Blaming',
                'level' => 'intermediate',
                'answer' => 'option3',
            ],
            // Customer Service
            [
                'competency_name' => 'Customer Service',
                'question' => 'What is the main goal of customer service?',
                'option1' => 'To increase sales',
                'option2' => 'To reduce costs',
                'option3' => 'To satisfy customers',
                'option4' => 'To manage employees',
                'level' => 'intermediate',
                'answer' => 'option3',
            ],
            [
                'competency_name' => 'Customer Service',
                'question' => 'Which of the following is a key aspect of good customer service?',
                'option1' => 'Ignoring complaints',
                'option2' => 'Listening to customers',
                'option3' => 'Avoiding customers',
                'option4' => 'Being unavailable',
                'level' => 'intermediate',
                'answer' => 'option2',
            ],
            [
                'competency_name' => 'Critical Thinking',
                'question' => 'What is critical thinking?',
                'option1' => 'Accepting all information as true',
                'option2' => 'Analyzing and evaluating information to form a reasoned judgment',
                'option3' => 'Ignoring conflicting information',
                'option4' => 'Relying on emotions for decision-making',
                'level' => 'intermediate',
                'answer' => 'option2',
            ],
            [
                'competency_name' => 'Critical Thinking',
                'question' => 'Which of the following is a characteristic of a critical thinker?',
                'option1' => 'Impulsiveness',
                'option2' => 'Open-mindedness',
                'option3' => 'Prejudice',
                'option4' => 'Stubbornness',
                'level' => 'intermediate',
                'answer' => 'option2',
            ],
            // Decision Making
            [
                'competency_name' => 'Decision Making',
                'question' => 'What is the first step in the decision-making process?',
                'option1' => 'Implementing the decision',
                'option2' => 'Identifying the decision to be made',
                'option3' => 'Gathering information',
                'option4' => 'Evaluating alternatives',
                'level' => 'intermediate',
                'answer' => 'option2',
            ],
            [
                'competency_name' => 'Decision Making',
                'question' => 'Which of the following is a decision-making technique?',
                'option1' => 'Guessing',
                'option2' => 'Coin flipping',
                'option3' => 'SWOT analysis',
                'option4' => 'Avoiding decisions',
                'level' => 'intermediate',
                'answer' => 'option3',
            ],
            // Project Management
            [
                'competency_name' => 'Project Management',
                'question' => 'What is the primary goal of project management?',
                'option1' => 'To manage resources effectively',
                'option2' => 'To complete the project on time, within scope and budget',
                'option3' => 'To delegate all tasks',
                'option4' => 'To avoid risks',
                'level' => 'intermediate',
                'answer' => 'option2',
            ],
            [
                'competency_name' => 'Project Management',
                'question' => 'Which of the following is a project management methodology?',
                'option1' => 'Lean',
                'option2' => 'Waterfall',
                'option3' => 'Six Sigma',
                'option4' => 'Kanban',
                'level' => 'intermediate',
                'answer' => 'option2',
            ],
            // Emotional Intelligence
            [
                'competency_name' => 'Emotional Intelligence',
                'question' => 'Which of the following is a component of emotional intelligence?',
                'option1' => 'Self-awareness',
                'option2' => 'Aggressiveness',
                'option3' => 'Disorganization',
                'option4' => 'Passivity',
                'level' => 'intermediate',
                'answer' => 'option1',
            ],
            [
                'competency_name' => 'Emotional Intelligence',
                'question' => 'Which of these best describes empathy?',
                'option1' => 'Ignoring others’ feelings',
                'option2' => 'Understanding and sharing the feelings of others',
                'option3' => 'Being indifferent to others',
                'option4' => 'Suppressing emotions',
                'level' => 'intermediate',
                'answer' => 'option2',
            ],
            // Negotiation
            [
                'competency_name' => 'Negotiation',
                'question' => 'What is the primary objective of negotiation?',
                'option1' => 'To win at all costs',
                'option2' => 'To reach a mutually beneficial agreement',
                'option3' => 'To avoid conflict',
                'option4' => 'To assert dominance',
                'level' => 'intermediate',
                'answer' => 'option2',
            ],
            [
                'competency_name' => 'Negotiation',
                'question' => 'Which of the following is a negotiation strategy?',
                'option1' => 'Ignoring the other party',
                'option2' => 'Active listening',
                'option3' => 'Being inflexible',
                'option4' => 'Avoiding compromise',
                'level' => 'intermediate',
                'answer' => 'option2',
            ],
            // Customer Service
            [
                'competency_name' => 'Customer Service',
                'question' => 'What is the main goal of customer service?',
                'option1' => 'To increase sales',
                'option2' => 'To reduce costs',
                'option3' => 'To satisfy customers',
                'option4' => 'To manage employees',
                'level' => 'intermediate',
                'answer' => 'option3',
            ],
            [
                'competency_name' => 'Customer Service',
                'question' => 'Which of the following is a key aspect of good customer service?',
                'option1' => 'Ignoring complaints',
                'option2' => 'Listening to customers',
                'option3' => 'Avoiding customers',
                'option4' => 'Being unavailable',
                'level' => 'intermediate',
                'answer' => 'option2',
            ],
            // Creativity
            [
                'competency_name' => 'Creativity',
                'question' => 'What is a common technique used to stimulate creativity?',
                'option1' => 'Mind mapping',
                'option2' => 'Linear thinking',
                'option3' => 'Following strict rules',
                'option4' => 'Routine tasks',
                'level' => 'intermediate',
                'answer' => 'option1',
            ],
            [
                'competency_name' => 'Creativity',
                'question' => 'Which of these is an example of divergent thinking?',
                'option1' => 'Finding a single solution to a problem',
                'option2' => 'Generating multiple solutions to a problem',
                'option3' => 'Analyzing dagta',
                'option4' => 'Following a predefined path',
                'level' => 'advance',
                'answer' => 'option2',
            ],
            // Time Management
            [
                'competency_name' => 'Time Management',
                'question' => 'Which of the following is a technique for effective time management?',
                'option1' => 'Procrastination',
                'option2' => 'Multitasking',
                'option3' => 'Setting priorities',
                'option4' => 'Daydreaming',
                'level' => 'intermediate',
                'answer' => 'option3',
            ],
            [
                'competency_name' => 'Time Management',
                'question' => 'What is a common tool used in time management?',
                'option1' => 'Calendar',
                'option2' => 'Clock',
                'option3' => 'Calculator',
                'option4' => 'Phone',
                'level' => 'intermediate',
                'answer' => 'option1',
            ],
        ];

        foreach ($questions as $question) {
            EvaluationQuestionBank::create($question);
        }
    }
}
