<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;



class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		// Default Roles
		$data = [
			[ 'name' => 'Owner', 'is_custom' => false, 'is_owner' => true, 'sort_order' => 0, 'description' => 'Highest level of permissions.'],
			[ 'name' => 'Admin', 'is_custom' => false, 'is_admin' => true, 'sort_order' => 1, 'description' => 'High level of permissions but cannot manage owners.' ],
			[ 'name' => 'Operations', 'is_custom' => false, 'sort_order' => 2, 'description' => null ],
			[ 'name' => 'Customer Support', 'is_custom' => false, 'sort_order' => 3, 'description' => null ],
            [ 'name' => 'Developer Support', 'is_custom' => false, 'sort_order' => 4, 'description' => null ],
		];

		foreach ($data as $row) {
			Role::updateOrCreate(
				['name' => $row['name'], 'is_custom' => false], 
				$row
			);
		}
		
		
		// Default Permissions
		$defaultPermissions = Permission::defaultPermissions();
		foreach ($defaultPermissions as $permission) {
			Permission::updateOrCreate(
				['name' => $permission['name']], 
				$permission
			);
		}
    }
}


