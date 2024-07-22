<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NotificationContent;

class NotificationContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        NotificationContent::create([
            'type' => 'employee_assigned_assessment',
            'subject' => 'New Assessment Assigned',
            'message_template' => 'Hello, {name}. A new assessment has been assigned to you. Please log in to view the details.'
        ]);


        NotificationContent::create([
            'type' => 'supervisor_assigned_assessment',
            'subject' => 'Manage Assessment',
            'message_template' => 'Hello, {name}. A new assessment has been assigned for you to manage. Please log in to view the details.'
        ]);
    }
}
