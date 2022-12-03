<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CountrySeeder::class);
		$this->call(StateSeeder::class);
        $this->call(TimezoneSeeder::class);
        
        $this->call(RoleAndPermissionSeeder::class);
        $this->call(UserSeeder::class);

        $this->call(SkillSeeder::class);
        $this->call(IndustrySeeder::class);
        $this->call(JobTitleSeeder::class);
        $this->call(DegreeSeeder::class);
        $this->call(CourseOfStudySeeder::class);
        $this->call(CertificationCourseSeeder::class);
        $this->call(LanguageSeeder::class);
        $this->call(SubscriptionPlanSeeder::class);
        $this->call(ReferenceQuestionSeeder::class);

        // php artisan db:seed --class=UserSeeder
        // php artisan db:seed --class=ReferenceQuestionSeeder
        // php artisan db:seed --class=ReferenceQuestionSeeder
    }
}
