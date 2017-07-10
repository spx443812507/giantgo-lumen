<?php

use Illuminate\Database\Seeder;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permission = [
            [
                'name' => 'role-list',
                'display_name' => 'Display Role Listing',
                'description' => 'See only Listing Of Role'
            ],
            [
                'name' => 'role-get',
                'display_name' => 'Display Role instance',
                'description' => 'See only instance Of Role'
            ],
            [
                'name' => 'role-create',
                'display_name' => 'Create Role',
                'description' => 'Create New Role'
            ],
            [
                'name' => 'role-edit',
                'display_name' => 'Edit Role',
                'description' => 'Edit Role'
            ],
            [
                'name' => 'role-delete',
                'display_name' => 'Delete Role',
                'description' => 'Delete Role'
            ],
            [
                'name' => 'entity-list',
                'display_name' => 'Display Entity Listing',
                'description' => 'See only Listing Of Entity'
            ],
            [
                'name' => 'entity-create',
                'display_name' => 'Create entity',
                'description' => 'Create New entity'
            ],
            [
                'name' => 'entity-edit',
                'display_name' => 'Edit entity',
                'description' => 'Edit entity'
            ],
            [
                'name' => 'entity-delete',
                'display_name' => 'Delete Role',
                'description' => 'Delete Role'
            ]
        ];

        foreach ($permission as $key => $value) {
            factory(App\Models\Permission::class)->create($value);
        }
    }
}
