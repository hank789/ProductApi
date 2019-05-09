<?php namespace App\Api\Controllers;
/**
 * @author: wanghui
 * @date: 2017/4/21 下午3:30
 * @email: hank.huiwang@gmail.com
 */


use App\Events\Frontend\System\ImportantNotify;
use App\Exceptions\ApiException;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Tag;
use App\Services\Hmac\Server;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class SystemController extends Controller {

    public function feedback(Request $request, JWTAuth $JWTAuth)
    {
        $validateRules = [
            'title'   => 'required',
            'content' => 'required'
        ];
        $this->validate($request, $validateRules);
        $source = '';
        if ($request->input('inwehub_user_device') == 'weapp_dianping') {
            $source = '小程序';
            $oauth = $JWTAuth->parseToken()->toUser();
            if ($oauth->user_id) {
                $user = $oauth->user;
            } else {
                $user = new \stdClass();
                $user->id = 0;
                $user->name = $oauth->nickname;
            }
        } else {
            $user = $request->user();
        }

        $fields = [];
        $fields[] = [
            'title'=>'内容',
            'value'=>$request->input('content')
        ];
        event(new ImportantNotify($source.formatSlackUser($user).$request->input('title'),$fields));
        return self::createJsonData(true);
    }

    //接收推送过来的文章
    public function pushArticle($product_id,Request $request) {
        $res = Server::instance()->validate($request->all());
        if ($res['code'] != 1000) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $product = Tag::find($product_id);
        if ($product) {
            $data = $request->all();
            $uuid = base64_encode($data['mp_id'].$data['title'].date('Y-m-d',strtotime($data['date_time'])));
            if (RateLimiter::instance()->hGet('wechat_article',$uuid)) {
                return self::createJsonData(true);
            }
            $article = WechatWenzhangInfo::create($data);
            RateLimiter::instance()->hSet('wechat_article',$uuid,$article->_id);
            $article->addProductTag();
        }
    }


}