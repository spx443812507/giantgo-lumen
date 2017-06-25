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
            'entity_type_name' => '管理员实体',
            'entity_type_code' => 'user',
            'entity_model' => 'App\Models\User',
            'entity_table' => 'users',
            'description' => '后台管理员用户实体，entity_type_id为 1 的全部为后台账号',
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime()
        ]);
    }
}
