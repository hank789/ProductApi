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

class SetProductManager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:set-product-manager {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '设置用户为产品管理员';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $id = $this->argument('id');
        $user = User::find($id);
        if (!$user) {
            $this->warn('该用户不存在');
            return;
        }
        $rel = ProductUserRel::first();
        ProductUserRel::create([
            'user_id' => $user->id,
            'status' => 1,
            'tag_id' => $rel->tag_id
        ]);
        $this->info('设置成功！');
    }
}
