<?php namespace App\Console\Commands\Init;
/**
 * @author: wanghui
 * @date: 2017/4/13 下午8:36
 * @email: hank.huiwang@gmail.com
 */
use App\Models\ProductUserRel;
use App\Models\Tag;
use App\Services\Hmac\Client;
use Illuminate\Console\Command;

class InitProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化产品';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $app_id = config('app.app_id');
        $app_secret = config('app.app_secret');
        if (!$app_id || !$app_secret) {
            $this->error('认证密匙未配置');
            return;
        }
        $res = Client::instance()->request(config('app.partner_service_url').'/api/partner/service/getProductInfo',['api_url'=>config('app.url')]);
        //var_dump($res);
        if ($res && $res['code'] == 1000) {
            $data = $res['data'];
            $product = Tag::find($data['id']);
            if (!$product) {
                $data['reviews'] = 0;
                $data['followers'] = 0;
                $data['parent_id'] = 0;
                Tag::create($data);
                ProductUserRel::create([
                    'user_id' => 1,
                    'status' => 1,
                    'tag_id' => $data['id']
                ]);
            }
            return;
        }
        $this->error('请求服务识别，请联系管理员或稍后再试');
    }
}
