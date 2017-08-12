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
                'client_id' => '101405226',
                'client_secret' => '22350fe680bec6a854f715fe149ec747',
                'redirect' => 'http://test-memberb.smarket.net.cn/oauth/qq/callback',
                'provider' => 'qq'
            ],
            [
                'client_id' => 'wx8f6f554a22bb28db',
                'client_secret' => '4681fc6e3ff1c1530544761e57562922',
                'redirect' => 'http://passport.smarket.net.cn/oauth/wechat/callback',
                'provider' => 'wechat'
            ],
            [
                'client_id' => 'wx414a6131135563d7',
                'client_secret' => '9e7c854999c9805b8fd83d8d52c790be',
                'redirect' => 'http://passport.smarket.net.cn/oauth/wechat_web/callback',
                'provider' => 'wechat_web'
            ]
        ];

        foreach ($application as $key => $value) {
            factory(App\Models\Application::class)->create($value);
        }
    }
}
