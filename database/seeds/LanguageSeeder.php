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

            ['name' => 'English', 'description' => 'Global lingua franca, widely used in business and technology'],
            ['name' => 'Mandarin Chinese', 'description' => 'Most spoken language globally, important for Asian markets'],
            ['name' => 'Spanish', 'description' => 'Second most spoken language globally, key for Latin American markets'],
            ['name' => 'Arabic', 'description' => 'Important for Middle Eastern and North African markets'],
            ['name' => 'French', 'description' => 'Official language in many African countries and international organizations'],
            ['name' => 'Portuguese', 'description' => 'Important for Brazilian and some African markets'],
            ['name' => 'Russian', 'description' => 'Widely spoken in Eastern Europe and Central Asia'],
            ['name' => 'German', 'description' => 'Key language for European business and engineering'],
            ['name' => 'Japanese', 'description' => 'Important for technology and business in Asia'],
            ['name' => 'Korean', 'description' => 'Growing importance in technology and entertainment industries'],
		];



		foreach ($data as $row) {
			Language::updateOrCreate(
				['name' => $row['name']],
				$row
			);
		}
    }
}
