<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Artisan;

class UpdatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run seeder to updates the available permissions.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // call php artisan to run seeder
        Artisan::call('db:seed --class=RoleAndPermissionSeeder');
        
        $this->info("Permissions updated...");

    }
}

