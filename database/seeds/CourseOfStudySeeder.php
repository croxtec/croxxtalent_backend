<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseOfStudy;

class CourseOfStudySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$data = [
			[ 'name' => 'Computer Science', 'description' => null ],
			[ 'name' => 'Computer Engineering', 'description' => null ],
			[ 'name' => 'Production Engineering', 'description' => null ],
			[ 'name' => 'Electrical Engineering', 'description' => null ],
			[ 'name' => 'Mechanical Engineering', 'description' => null ],
			[ 'name' => 'Banking & Finance', 'description' => null ],
			[ 'name' => 'Accountancy', 'description' => null ],
			[ 'name' => 'Chemical Engineer', 'description' => null ],
			[ 'name' => 'Mathematics', 'description' => null ],
			[ 'name' => 'Statistics', 'description' => null ],
		];

		foreach ($data as $row) {
			CourseOfStudy::updateOrCreate(
				['name' => $row['name']], 
				$row
			);
		}
    }
}
