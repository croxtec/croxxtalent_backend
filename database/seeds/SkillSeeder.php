<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Skill;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$data = [
			[ 'name' => 'SEO', 'description' => null ],
			[ 'name' => 'Public Speaking', 'description' => null ],
			[ 'name' => 'Negotiation', 'description' => null ],
			[ 'name' => 'Teamwork', 'description' => null ],
			[ 'name' => 'Decision Making', 'description' => null ],
			[ 'name' => 'Research & Strategy', 'description' => null ],
			[ 'name' => 'Emotional Intelligence', 'description' => null ],
			[ 'name' => 'Outbound Marketing', 'description' => null ],
			[ 'name' => 'Email Marketing', 'description' => null ],
			[ 'name' => 'Google Analytics', 'description' => null ],
			[ 'name' => 'Sales & Marketing', 'description' => null ],
		];

		foreach ($data as $row) {
			Skill::updateOrCreate(
				['name' => $row['name']], 
				$row
			);
		}
    }
}
