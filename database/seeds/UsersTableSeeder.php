<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/3/31
 * Time: 下午5:37
 */

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = factory(App\Models\User::class)->create([
            'email' => 'admin@admin.com',
            'mobile' => '15930181489',
            'password' => 'admin',
            'last_login' => new DateTime()
        ]);

        $adminRole = factory(App\Models\Role::class)->create([
            'name' => 'admin',
            'display_name' => '系统管理员',
            'description' => '系统管理员，拥有整个系统最大权限',
        ]);

        factory(App\Models\Role::class)->create([
            'name' => 'owner',
            'display_name' => '项目所有者',
            'description' => '项目所有者，与其他项目隔离',
        ]);

        factory(App\Models\Role::class)->create([
            'name' => 'customer',
            'display_name' => '联系人',
            'description' => '联系人',
        ]);

        $user->attachRole($adminRole);

        factory(App\Models\EAV\Factories\EntityFactory::class)->create([
            'id' => 1,
            'user_id' => $user->id,
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