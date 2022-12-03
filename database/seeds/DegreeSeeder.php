<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Degree;

class DegreeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$data = [
			[ 'name' => 'Diploma', 'description' => null ],
			[ 'name' => 'High School (S.S.C.E)', 'description' => null ],
			[ 'name' => 'HND', 'description' => null ],
			[ 'name' => 'BSc', 'description' => null ],
			[ 'name' => 'MBA', 'description' => null ],
			[ 'name' => 'MSc', 'description' => null ],
			[ 'name' => 'MBBS', 'description' => null ],
			[ 'name' => 'MPhil', 'description' => null ],
			[ 'name' => 'PhD', 'description' => null ],
			[ 'name' => 'N.C.E', 'description' => null ],
			[ 'name' => 'OND', 'description' => null ],
			[ 'name' => 'Vocational', 'description' => null ],
			[ 'name' => 'Others', 'description' => null ],
		];

		foreach ($data as $row) {
			Degree::updateOrCreate(
				['name' => $row['name']], 
				$row
			);
		}
    }
}
