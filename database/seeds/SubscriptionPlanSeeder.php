<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
			[ 'type' => 'employer', 'name' => 'Basic', 'interval' => 'monthly', 'duration' => 1, 'currency_code' => 'NGN', 'amount' => 5000, 'discount_percentage' => null, 'description' => null ],
			[ 'type' => 'employer', 'name' => 'Basic', 'interval' => 'yearly', 'duration' => 1, 'currency_code' => 'NGN', 'amount' => 60000, 'discount_percentage' => 10, 'description' => null ],

		];

		foreach ($data as $row) {
			SubscriptionPlan::updateOrCreate(
				[
                    'type' => $row['type'],
                    'name' => $row['name'], 
                    'interval' => $row['interval'], 
                    'duration' => $row['duration'], 
                    'currency_code' => $row['currency_code']
                ], 
				$row
			);
		}
    }
}
