<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$data = [
			[ 'name' => 'English', 'description' => null ],
			[ 'name' => 'French', 'description' => null ],
			[ 'name' => 'Spanish', 'description' => null ],
			[ 'name' => 'German', 'description' => null ],
		];

		foreach ($data as $row) {
			Language::updateOrCreate(
				['name' => $row['name']], 
				$row
			);
		}
    }
}
