<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CertificationCourse;

class CertificationCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$data = [
			[ 'name' => 'Digital Marketing', 'description' => null ],
			[ 'name' => 'Project Management', 'description' => null ],
			[ 'name' => 'CISCO', 'description' => null ],
			[ 'name' => 'CompTIA A+', 'description' => null ],
			[ 'name' => 'Risk Management', 'description' => null ],
			[ 'name' => 'Data Science', 'description' => null ],
			[ 'name' => 'Artificial Intelligence', 'description' => null ],
		];

		foreach ($data as $row) {
			CertificationCourse::updateOrCreate(
				['name' => $row['name']], 
				$row
			);
		}
    }
}
