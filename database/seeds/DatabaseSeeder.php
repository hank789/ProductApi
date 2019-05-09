<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * 数据库初始化
     *
     * @return void
     */
    public function run()
    {
        $registrar = new \App\Services\Registrar();
        $admin = $registrar->create([
            'name' => 'InweHub',
            'email' => 'hank.wang@inwehub.com',
            'mobile' => '15050368286',
            'password' => 'qwer1234',
            'status' => 1,
            'visit_ip' => '127.0.0.1',
        ]);
        $admin->attachRole(1);
    }
}
