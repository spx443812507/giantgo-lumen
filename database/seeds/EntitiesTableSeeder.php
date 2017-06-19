<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('entity_type')->insert([
            'id' => 1,
            'entity_type_code' => 'user',
            'entity_model' => 'App\Models\User',
            'attribute_model' => '',
            'entity_table' => 'users',
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime()
        ]);

        $attributes = [
            [
                'id' => 1,
                'entity_type_id' => 1,
                'attribute_code' => 'name',
                'attribute_model' => '',
                'backend_model' => '',
                'backend_type' => 'App\Models\EAV\Types\Varchar',
                'backend_table' => '',
                'frontend_model' => '',
                'frontend_input' => 'text',
                'frontend_label' => '姓名',
                'frontend_class' => '',
                'is_required' => true,
                'is_user_defined' => false,
                'is_unique' => false,
                'default_value' => '张三',
                'description' => '该字段记录用户姓名',
            ],
            [
                'id' => 2,
                'entity_type_id' => 1,
                'attribute_code' => 'age',
                'attribute_model' => '',
                'backend_model' => '',
                'backend_type' => 'App\Models\EAV\Types\Integer',
                'backend_table' => '',
                'frontend_model' => '',
                'frontend_input' => 'number',
                'frontend_label' => '年龄',
                'frontend_class' => '',
                'is_required' => false,
                'is_user_defined' => false,
                'is_unique' => false,
                'default_value' => '18',
                'description' => '该字段记录用户年龄',
            ]
        ];

        foreach ($attributes as $key => $value) {
            factory(App\Models\EAV\Attribute::class)->create($value);
        }
    }
}
