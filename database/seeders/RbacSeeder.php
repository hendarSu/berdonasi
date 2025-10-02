<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RbacSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $perms = [
            'manage users',
            'manage roles',
            'manage permissions',
            'view campaign',
            'create campaign',
            'update campaign',
            'delete campaign',
        ];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }
        $admin->givePermissionTo($perms);


        $user = \App\Models\User::where('email', 'admin@myapp.test')->first();
        $user?->assignRole('admin');
    }
}
