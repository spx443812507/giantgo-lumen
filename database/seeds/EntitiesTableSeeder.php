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
        factory(App\Models\EAV\Factories\EntityFactory::class)->create([
            'id' => 1,
            'entity_type_name' => '华为员工模型',
            'entity_type_code' => 'user',
            'entity_model' => 'App\Models\User',
            'entity_table' => 'users',
            'description' => '用户自定义模型',
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime()
        ]);
    }
}
