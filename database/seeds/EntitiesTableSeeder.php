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
        DB::table('eav_entity_type')->insert([
            'entity_type_id' => 1,
            'entity_type_code' => 'user',
            'entity_model' => 'App\Models\User',
            'attribute_model' => '',
            'entity_table' => 'users',
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime()
        ]);
    }
}
