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
            'name' => '申鹏翔',
            'company' => '赛诺贝斯',
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

        $user->attachRole($adminRole);
    }
}