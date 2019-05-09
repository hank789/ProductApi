<?php namespace App\Console\Commands\Init;
/**
 * @author: wanghui
 * @date: 2017/4/13 下午8:36
 * @email: hank.huiwang@gmail.com
 */
use App\Models\ProductUserRel;
use App\Models\Tag;
use App\Models\User;
use App\Services\Hmac\Client;
use App\Services\Registrar;
use Illuminate\Console\Command;

class AddUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:add-user {name} {phone} {manager}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '添加用户';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');
        $phone = $this->argument('phone');
        $manager = $this->argument('manager');
        $user = User::where('mobile')->first();
        if ($user) {
            $this->warn('该用户已存在,id:'.$user->id);
            return;
        }
        $register = new Registrar();
        $user = $register->create([
            'name' => $name,
            'email' => '',
            'mobile' => $phone,
            'password' => time(),
            'status' =>1
        ]);
        if ($manager) {
            $rel = ProductUserRel::first();
            ProductUserRel::create([
                'user_id' => $user->id,
                'status' => 1,
                'tag_id' => $rel->tag_id
            ]);
        }
        $this->info('添加成功！');
    }
}
