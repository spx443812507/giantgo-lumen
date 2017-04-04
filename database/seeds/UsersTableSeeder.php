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
        factory(App\Models\User::class)->create([
            'name' => 'siler',
            'email' => 'spx@foxmail.com',
            'mobile' => '15930181489',
            'password' => app('hash')->make('123123'),
            'last_login' => new DateTime()
        ]);
    }
}