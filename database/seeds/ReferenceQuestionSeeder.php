<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReferenceQuestion;
use App\Models\ReferenceQuestionOption;

class ReferenceQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
            Use placeholders in questions and descriptions
            {{talent_name}} - Displays talent first and last name
            {{talent_first_name}} - Displays talent first name
            {{talent_last_name}} - Displays talent last name
        */
        // Reference Questions
		$questions = [
            [
                'sort_order' => 1, 
                'is_active' => true,
                'question' => "How long have you known the candidate?",
                'description' => null,
                'is_predefined_options' => false, 
                'options' => null,
                'use_textarea' => false, 
            ],
            [
                'sort_order' => 2, 
                'is_active' => true,
                'question' => "What is the nature of your relationship with the candidate?",
                'description' => null,
                'is_predefined_options' => false, 
                'options' => null,
                'use_textarea' => false, 
            ],
            [
                'sort_order' => 3, 
                'is_active' => true,
                'question' => "How would you describe the candidate's reliability and dependability?",
                'description' => null,
                'is_predefined_options' => false, 
                'options' => null,
                'use_textarea' => true, 
            ],
            [
                'sort_order' => 4, 
                'is_active' => true,
                'question' => "What are the candidate's strengths and weaknesses?",
                'description' => null,
                'is_predefined_options' => false, 
                'options' => null,
                'use_textarea' => true, 
            ],
            [
                'sort_order' => 5, 
                'is_active' => true,
                'question' => "What was one of the candidate's most memorable accomplishments while working with you?",
                'description' => null,
                'is_predefined_options' => false, 
                'options' => null,
                'use_textarea' => true, 
            ],
            [
                'sort_order' => 6, 
                'is_active' => true,
                'question' => "What type of work environment do you think the candidate would be most likely to thrive in, and why?",
                'description' => null,
                'is_predefined_options' => false, 
                'options' => null,
                'use_textarea' => true, 
            ],
            [
                'sort_order' => 7, 
                'is_active' => true,
                'question' => "What skills would you have liked to see the candidate develop to reach their full potential?",
                'description' => null,
                'is_predefined_options' => false, 
                'options' => null,
                'use_textarea' => true, 
            ],
            [
                'sort_order' => 8, 
                'is_active' => true,
                'question' => "Would you recommend this candidate?",
                'description' => null,
                'is_predefined_options' => true, 
                'options' => ['Yes', 'No'],
                'use_textarea' => false, 
            ],
            [
                'sort_order' => 9, 
                'is_active' => true,
                'question' => "Would you rehire the candidate; why or why not?",
                'description' => null,
                'is_predefined_options' => false, 
                'options' => null,
                'use_textarea' => true, 
            ],
            [
                'sort_order' => 10, 
                'is_active' => true,
                'question' => "What is the best time to contact you and how do we contact you?",
                'description' => null,
                'is_predefined_options' => false, 
                'options' => null,
                'use_textarea' => false, 
            ],
            [
                'sort_order' => 11, 
                'is_active' => true,
                'question' => "Is there anything else you would like us to know about the candidate?",
                'description' => null,
                'is_predefined_options' => false, 
                'options' => null,
                'use_textarea' => true, 
            ],
        ];
        
        // deactivate all previously saved questions not on the list above
        ReferenceQuestion::where('is_active', true)
                            ->update(['is_active' => false]);

        // Save the questions
		foreach ($questions as $row) {
            $question_options = $row['options'];
            unset($row['options']);
			$referenceQuestion = ReferenceQuestion::updateOrCreate(
				['question' => $row['question']], 
				$row
            );
            if ($referenceQuestion) {
                if (isset($question_options) && is_array($question_options)) {
                    foreach ($question_options as $option_index=>$option) {
                        ReferenceQuestionOption::updateOrCreate(
                            ['reference_question_id' => $referenceQuestion->id, 'name' => $option]
                        );
                    }
                } else {
                    $referenceQuestion->is_predefined_options = false;
                    $referenceQuestion->save();
                }
            }
		}
    }
}
