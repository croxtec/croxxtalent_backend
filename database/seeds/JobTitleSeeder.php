<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobTitle;

class JobTitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$data = [
			[ 'name' => 'Accountant', 'description' => null ],
			[ 'name' => 'Industrial Engineer', 'description' => null ],
			[ 'name' => 'Customer Service & Support', 'description' => null ],
			[ 'name' => 'Drilling Engineer', 'description' => null ],
			[ 'name' => 'Driver', 'description' => null ],
			[ 'name' => 'Project Manager', 'description' => null ],
		];

		foreach ($data as $row) {
			JobTitle::updateOrCreate(
				['name' => $row['name']], 
				$row
			);
		}
    }
}
