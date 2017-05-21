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
                'name' => 'product-list',
                'display_name' => 'Display product Listing',
                'description' => 'See only Listing Of product'
            ],
            [
                'name' => 'product-create',
                'display_name' => 'Create product',
                'description' => 'Create New product'
            ],
            [
                'name' => 'product-edit',
                'display_name' => 'Edit product',
                'description' => 'Edit product'
            ],
            [
                'name' => 'product-delete',
                'display_name' => 'Delete product',
                'description' => 'Delete product'
            ]
        ];

        foreach ($permission as $key => $value) {
            factory(App\Models\Permission::class)->create($value);
        }
    }
}
