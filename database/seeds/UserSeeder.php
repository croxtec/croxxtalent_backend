<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		// Fetch or create first owner role
        $ownerRole = Role::firstOrCreate(
            ['name' => 'Owner'], 
            ['is_custom' => false, 'is_owner' => true, 'sort_order' => 0]
        );       
		if ($ownerRole) {
            // Create first user
            $user = User::firstOrCreate(
                ['email' => 'admin@croxxtalent.com'],
                [
                    'type' => 'admin',
                    'first_name' => 'Croxx',
                    'last_name' => 'Talent',
                    'password' => 'admin', // password will be hashed by model set attribute
                ]
            );
            if ( $user ) {
                // add the user role as owner
                $user->role_id = $ownerRole->id;
                $user->is_active = true;
                $user->save();
                
            } else {
                throw new ModelNotFoundException('Could not create user "admin@croxxtalent.com" as owner.');
            }
        } else {
			throw new ModelNotFoundException('Could not fetch or create role "onwer" record.');
		}
    }
}


