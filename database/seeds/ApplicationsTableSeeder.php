<?php

use Illuminate\Database\Seeder;

class ApplicationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $application = [
            [
                'client_id' => '',
                'client_secret' => '',
                'redirect' => '/oauth/qq/callback',
                'provider' => 'qq'
            ],
            [
                'client_id' => '',
                'client_secret' => '',
                'redirect' => '/oauth/wechat/callback',
                'provider' => 'wechat'
            ],
            [
                'client_id' => '',
                'client_secret' => '',
                'redirect' => '/oauth/wechat_web/callback',
                'provider' => 'wechat_web'
            ]
        ];

        foreach ($application as $key => $value) {
            factory(App\Models\Application::class)->create($value);
        }
    }
}
