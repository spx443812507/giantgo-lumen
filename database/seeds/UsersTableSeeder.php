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
        factory(App\User::class)->create([
            'email' => 'spx@foxmail.com',
            'password' => app('hash')->make('123123')
        ]);

        factory(App\User::class)->create([
            'email' => 'user2@example.com',
            'password' => app('hash')->make('1234')
        ]);

        factory(App\User::class)->create([
            'email' => 'user3@example.com',
            'password' => app('hash')->make('1234')
        ]);
    }
}