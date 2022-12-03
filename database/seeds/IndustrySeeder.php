<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Industry;

class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$data = [
			[ 'name' => 'Oil & Gas', 'description' => null ],
			[ 'name' => 'IT & Telecoms', 'description' => null ],
			[ 'name' => 'Mining, Energy & Metals', 'description' => null ],
		];

		foreach ($data as $row) {
			Industry::updateOrCreate(
				['name' => $row['name']], 
				$row
			);
		}
    }
}
