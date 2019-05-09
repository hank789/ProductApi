<?php
/**
 * Created by PhpStorm.
 * User: sdf_sky
 * Date: 15/10/27
 * Time: 下午7:11
 */



if (! function_exists('trans_gender_name')) {

    function trans_gender_name($post_type){
        $map = [
            0 => '保密',
            1 => '男',
            2 => '女',
        ];

        if($post_type==='all'){
            return $map;
        }


        if(isset($map[$post_type])){
            return $map[$post_type];
        }

        return '';

    }

}


/*公告状态文字定义*/
if (! function_exists('trans_common_status')) {

    function trans_common_status($status){
        $map = [
            0 => '待审核',
            1 => '已审核',
           -1 => '已禁止',
            2 => '已结束',
            3 => '待抓取'
        ];

        if($status==='all'){
            return $map;
        }


        if(isset($map[$status])){
            return $map[$status];
        }

        return '';

    }
}

/*公告状态文字定义*/
if (! function_exists('trans_article_status')) {

    function trans_article_status($status){
        $map = [
            1 => '待发布',
            2 => '已发布',
            3 => '已删除',
        ];

        if($status==='all'){
            return $map;
        }


        if(isset($map[$status])){
            return $map[$status];
        }

        return '';

    }
}





/*数据库Category表操作*/
if (! function_exists('load_categories')) {

    function load_categories( $type = 'all' , $root = false , $last = false){
        return app('App\Models\Category')->loadFromCache($type,$root, $last);
    }

}


/**
 * 将正整数转换为带+,例如 10 装换为 +10
 * 用户积分显示
 */
if( ! function_exists('integer_string')){
    function integer_string($value){
        if($value>=0){
            return '+'.$value;
        }

        return $value;
    }
}


/*常见的正则判断*/

/*邮箱判断*/
if( !function_exists('is_email') ){
    function is_email($email){
        $reg = "/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/";
        if( preg_match($reg,$email) ){
            return true;
        }
        return false;
    }
}

/*手机号码判断*/
if( !function_exists('is_mobile') ){
    function is_mobile($mobile){
        $reg = "/^1[34578]\d{9}$/";
        if( !preg_match($reg,$mobile) ){
            return false;
        }
        return true;
    }
}

if (!function_exists('secret_mobile')) {
    function secret_mobile($mobile) {
        return substr($mobile, 0, 5).'****'.substr($mobile, 9);
    }
}

//生成验证码
if( !function_exists('makeVerifyCode') ){
    function makeVerifyCode(int $min = 1000, int $max = 9999)
    {
        if(config('app.env') != 'production') return 6666;
        $min = min($min, $max);
        $max = max($min, $max);

        if (function_exists('mt_rand')) {
            return mt_rand($min, $max);
        }

        return rand($min, $max);
    }
}


if (!function_exists('gen_order_number')) {
    function gen_order_number($type='Order'){
        $time = date('YmdHis');
        /**
         * @var \Redis
         */
        $redis = Illuminate\Support\Facades\Redis::connection();
        $key = $type.$time;
        $count = $redis->incr($key);
        $redis->expire($key, 60);
        return $time.$count;
    }
}


if (!function_exists('gen_user_uuid')){
    function gen_user_uuid(){
        $uuid1 = \Ramsey\Uuid\Uuid::uuid1();
        return $uuid1->getHex();
    }
}

if (!function_exists('get_user_avatar_url_by_id')){
    function get_user_avatar_url_by_id($uid){
        $user = \App\Models\User::find($uid);
        return $user->getAvatarUrl();
    }
}


if (!function_exists('format_json_string')){
    function format_json_string($json,$field=''){
        $arr = json_decode($json,true);
        if($arr) {
            if($field){
                return implode(',',array_column($arr,$field));
            } else {
                return implode(',',array_values($arr));
            }
        }
        return '';
    }
}




if (!function_exists('string')){
    /**
     * @param string $string
     *
     * @return \App\Services\String\Str
     */
    function string($string = '')
    {
        return new \App\Services\String\Str($string);
    }
}

if (!function_exists('saveImgToCdn')){
    function saveImgToCdn($imgUrl,$dir = 'avatar', $isIco = false, $queue = true){
        $parse_url = parse_url($imgUrl);
        if (isset($parse_url['host']) && !in_array($parse_url['host'],['cdnread.ywhub.com','cdn.inwehub.com','inwehub-pro.oss-cn-zhangjiakou.aliyuncs.com','intervapp-test.oss-cn-zhangjiakou.aliyuncs.com'])) {
            $imgType = 'png';
            if (strrchr($parse_url['path'],'.svg') == '.svg') {
                $imgType = 'svg';
            }elseif (strrchr($parse_url['path'],'.gif') == '.gif') {
                $imgType = 'gif';
            }elseif ($isIco || strrchr($parse_url['path'],'.ico') == '.ico') {
                $imgType = 'ico';
            }
            $file_name = $dir.'/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$imgType;
            $ql = \QL\QueryList::getInstance();
            $gfw_urls = \App\Services\RateLimiter::instance()->sMembers('gfw_urls');
            if ($parse_url['host'] == 'mmbiz.qpic.cn' || $parse_url['host'] == 'mmbiz.qlogo.cn') {
                $otherArgs = [
                    'headers' => [
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                        'Host' => $parse_url['host'],
                        'cache-control' => 'no-cache',
                        'pragma' => 'no-cache',
                        'Upgrade-Insecure-Requests' => 1,
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36'
                    ]
                ];
            } else {
                $otherArgs = [
                    'headers' => [
                        'Referer' => $parse_url['host'],
                        'Host' => $parse_url['host'],
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36'
                    ]
                ];
            }
            try {
                if (in_array($parse_url['host'],[
                        'lh4.googleusercontent.com',
                        'lh3.googleusercontent.com'
                    ]) || str_contains($parse_url['host'],'googleusercontent.com') || str_contains($parse_url['host'],'medium.com') || in_array($parse_url['host'],$gfw_urls)) {
                    //判断是否需要翻墙
                    $otherArgs['proxy'] = 'socks5h://127.0.0.1:1080';
                }
                $content = $ql->get($imgUrl,null,$otherArgs)->getHtml();
                if ($queue) {
                    dispatch((new \App\Jobs\UploadFile($file_name,base64_encode($content))));
                } else {
                    Storage::disk('oss')->put($file_name,$content);
                }
                return Storage::url($file_name);
            } catch (Exception $e) {
                app('sentry')->captureException($e);
                return 'https://cdn.inwehub.com/system/group_18@3x.png';
            }

        }
        return $imgUrl;
    }
}


if (!function_exists('getUrlInfo')) {
    function getUrlInfo($url, $withImageUrl = false, $dir = 'submissions', $queue = true) {
        $img_url = Cache::get('url_img_'.$url,'');
        $title = Cache::get('url_title_'.$url, '');
        if ($title && $img_url) {
            return ['title'=>$title,'img_url'=>$img_url];
        }
        if ($title && !$withImageUrl) {
            return ['title'=>$title,'img_url'=>$img_url];
        }
        if ($img_url && $withImageUrl) {
            return ['title'=>$title,'img_url'=>$img_url];
        }
        $isIco = false;
        try {
            $temp = '';
            $useCache = false;
            $urlArr = parse_url($url);
            if (in_array($urlArr['host'],['web.ywhub.com','m.inwehub.com'])) {
                $params = explode('/', $urlArr['fragment']);
                if (isset($params[1]) && $params[1] == 'c') {
                    $slug = explode('?', $params[3]);
                    $submission = \App\Models\Submission::where('slug', $slug[0])->first();
                    $img = $submission->data['img'] ?? '';
                    if (is_array($img)) {
                        if ($img) {
                            $img = $img[0];
                        } else {
                            $img = '';
                        }
                    }
                    $title = strip_tags($submission->data['title'] ?? $submission->title);
                    $img_url = $img;
                } elseif (isset($params[1]) && $params[1] == 'ask') {
                    if ($params[3] == 'answers') {
                        $slug = explode('?', $params[4]);
                        $question = \App\Models\Question::find($slug[0]);
                    } else {
                        $slug = explode('?', $params[3]);
                        $answer = \App\Models\Answer::find($slug[0]);
                        $question = \App\Models\Question::find($answer->question_id);
                    }
                    $title = strip_tags($question->title);
                    $img_url = '';
                } elseif (isset($params[1]) && $params[1] == 'dianping') {
                    $slug = explode('?', $params[3]);
                    if ($params[2] == 'product') {
                        $tag = \App\Models\Tag::getTagByName(urldecode($slug[0]));
                        $title = $tag->name;
                        $img_url = $tag->logo;
                    } else {
                        $submission = \App\Models\Submission::where('slug', $slug[0])->first();
                        $tag = \App\Models\Tag::find($submission->category_id);
                        $title = strip_tags($submission->data['title'] ?? $submission->title);
                        $img_url = $tag->logo;
                    }
                }
                Cache::put('url_title_' . $url, $title, 60 * 24 * 7);
                Cache::put('url_img_' . $url, $img_url, 60 * 24 * 7);
                return ['title' => $title, 'img_url' => $img_url];
            } elseif ($urlArr['host']=='mp.weixin.qq.com') {
                $f = file_get_contents_curl($url);
                //微信的文章
                $pattern = '/var msg_cdn_url = "(.*?)";/s';
                preg_match_all($pattern,$f,$matches);
                if(array_key_exists(1, $matches) && !empty($matches[1][0])) {
                    $temp = $matches[1][0];
                    //将tp=webp为tp=jpg
                    $temp = str_replace('tp=webp','tp=jpg',$temp);
                }
                preg_match('/<h2 class="rich_media_title" id="activity-name">(?<h2>.*?)<\/h2>/si', $f, $title);
                if (isset($title['h2'])) {
                    $title = $title['h2'];
                } elseif (str_contains($f,'该公众号已迁移至新的帐号，原帐号已回收。')) {
                    //该微信文章已转移
                } else {
                    //该微信文章或已删除
                    $img_url = 'https://cdn.inwehub.com/system/group_18@3x.png';
                }
            } else {
                $ql = \QL\QueryList::getInstance();
                $gfw_urls = \App\Services\RateLimiter::instance()->sMembers('gfw_urls');
                if (in_array($urlArr['host'],[
                    'www.bilibili.com'
                ])) {
                    $ql->use(\QL\Ext\PhantomJs::class,config('services.phantomjs.path'));
                    $ql->browser($url);

                } elseif (in_array($urlArr['host'],$gfw_urls) && config('app.env') == 'production') {
                    $html = curlShadowsocks($url);
                    $ql->setHtml($html);
                }
                else {
                    $ql->get($url,null,['timeout'=>15]);
                }
                $image = $ql->find('meta[property=og:image]')->content;
                if (!$image) {
                    $image = $ql->find('meta[name=image]')->content;
                }
                if ($urlArr['host'] == 'm.jiemian.com') {
                    $image = $ql->find('div.wechat_logo>img')->src;
                }
                if (!$image) {
                    $image = $ql->find('meta[itemprop=image]')->content;
                    if (!$image && false) {
                        $image = $ql->find('link[rel=icon]')->href;
                        if (!$image) {
                            $image = $ql->find('link[rel=shortcut icon]')->href;
                            if (!$image) {
                                $image = $ql->find('link[href*=.ico]')->href;
                                if (!$image) {
                                    if ($urlArr['host'] == 'www.iyiou.com') {
                                        $image = $ql->find('img.aligncenter')->src;
                                    } else {
                                        $image = $urlArr['scheme'].'://'.$urlArr['host'].'/favicon.ico';
                                        $isIco = true;
                                    }
                                } else {
                                    $isIco = true;
                                }
                            } else {
                                $isIco = true;
                            }
                        } else {
                            $isIco = true;
                        }
                    }
                }
                if (!$image) {
                    //$img_url = 'https://cdn.inwehub.com/system/group_18@3x.png';
                    //event(new \App\Events\Frontend\System\ExceptionNotify('未取到网站:'.$url.'的图片'));
                }
                $title = $ql->find('title')->eq(0)->text();
                if (str_contains($image,'.ico')) {
                    $useCache = true;
                    $img_url = Cache::get('domain_url_img_'.domain($url),'');
                }

                if (stripos($image,'//') === 0) {
                    $temp = 'http:'.$image;
                } elseif ($image && stripos($image,'http') !== 0) {
                    $temp = $urlArr['scheme'].'://'.$urlArr['host'].$image;
                } else {
                    $temp = $image;
                }
            }
            $encode = mb_detect_encoding($title); //得到字符串编码
            $file_charset = iconv_get_encoding()['internal_encoding']; //当前文件编码
            $title = trim($title);
            if ( $encode != 'CP936' && $encode && $encode != $file_charset) {
                $title = iconv($encode, $file_charset, $title);
            }
            if (str_contains($url,'3g.163.com')) {
                $title = trim($title,'_&#x624B;&#x673A;&#x7F51;&#x6613;&#x7F51;');
            }
            $title = htmlspecialchars_decode($title);
            Cache::put('url_title_'.$url,$title,60 * 24 * 7);
            if ($temp && $withImageUrl && !$img_url) {
                try {
                    //保存图片
                    $img_url = saveImgToCdn($temp,$dir,$isIco,$queue);
                    //非微信文章
                    if ($useCache) {
                        Cache::put('domain_url_img_'.domain($url),$img_url,60 * 24 * 30);
                    }
                    Cache::put('url_img_'.$url,$img_url,60 * 24 * 7);
                } catch (Exception $e) {
                    $img_url = 'https://cdn.inwehub.com/system/group_18@3x.png';
                }
            }
            return ['title'=>$title,'img_url'=>$img_url];
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            if (isset($urlArr)) {
                \App\Services\RateLimiter::instance()->sAdd('gfw_urls',$urlArr['host'],0);
            }
        } catch (Exception $e) {
            app('sentry')->captureException($e,['url'=>$url]);
            if (empty($img_url) && $urlArr['host'] =='www.linkedin.com') {
                $img_url = 'https://cdn.inwehub.com/system/favicon_linkedin.ico';
            }
            return ['title'=>$title,'img_url'=>$img_url];
        }
    }
}

if (!function_exists('domain')) {
    /**
     * Squeezes the domain address from a valid URL.
     *
     * @param string $url
     *
     * @return string
     */
    function domain($url)
    {
        return str_ireplace('www.', '', parse_url($url, PHP_URL_HOST));
    }
}

if (!function_exists('firstRate')) {
    /**
     * Calculates the rate for votable model (currently used for submissions and comments).
     *
     * @return float
     */
    function firstRate()
    {
        return date('Ymd').'0';
    }
}

if (!function_exists('getRequestIpAddress')) {
    /**
     * Returns the real IP address of the request even if the website is using Cloudflare.
     *
     * @return string
     */
    function getRequestIpAddress()
    {
        return $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

if (!function_exists('file_get_contents_curl')) {
    function file_get_contents_curl($url, $checkTitle = true)
    {
        $ch = curl_init();
        $headers = [];
        $headers[] = 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6';
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0';
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $data = curl_exec($ch);
        curl_close($ch);
        if ($checkTitle) {
            preg_match('/<title>(?<title>.*?)<\/title>/si', $data, $title);
            if (empty($title)) {
                $ql = \QL\QueryList::getInstance();
                $ql->use(\QL\Ext\PhantomJs::class,config('services.phantomjs.path'));
                $data = $ql->browser($url)->getHtml();
            }
        }
        return $data;
    }
}

if (!function_exists('rateSubmission')) {
    /**
     * Calculates the rate for sorting by hot.
     *
     * @param int       $upvotes
     * @param int       $downvotes
     * @param timestamp $created
     *
     * @return float
     */
    function rateSubmission($upvotes, $downvotes, $created)
    {
        $startTime = 1473696439; // strtotime('2016-09-12 16:07:19')
        $created = strtotime($created);
        $timeDiff = $created - $startTime;

        $x = $upvotes - $downvotes;

        if ($x > 0) {
            $y = 1;
        } elseif ($x == 0) {
            $y = 0;
        } else {
            $y = -1;
        }

        if (abs($x) >= 1) {
            $z = abs($x);
        } else {
            $z = 1;
        }

        return (log10($z) * $y) + ($timeDiff / 45000);
    }
}

if (!function_exists('hotRate')) {
    /**
     * http://www.ruanyifeng.com/blog/2012/03/ranking_algorithm_stack_overflow.html
     * https://www.biaodianfu.com/stackoverflow-ranking-algorithm.html
     * http://meta.stackoverflow.com/questions/11602/what-formula-should-be-used-to-determine-hot-questions
     * @param $Qviews
     * @param $Qanswers
     * @param $Qscore
     * @param $Ascores
     * @param $date_ask
     * @param $date_active
     * @return float|int
     */
    function hotRate($Qviews, $Qanswers, $Qscore, $Ascores, $date_ask, $date_active)
    {
        $Qage = (time() - strtotime(gmdate("Y-m-d H:i:s",strtotime($date_ask)))) / 3600;
        $Qage = round($Qage, 1);

        $Qupdated = (time() - strtotime(gmdate("Y-m-d H:i:s",strtotime($date_active)))) / 3600;
        $Qupdated = round($Qupdated, 1);
        if ($Qanswers<=0 && $Qscore!=0) {
            $Qanswers = 1;
        }
        if ($Qanswers!=0 && $Qscore==0) {
            $Qscore = 1;
        }
        $dividend = (log10($Qviews)*4) + (($Qanswers * $Qscore)/2) + $Ascores;
        $divisor = pow((($Qage + 1) - ($Qage - $Qupdated)/2), 1.5);
        return bcdiv($dividend,$divisor,10);
    }
}

if (!function_exists('getDistanceByLatLng')) {
    function getDistanceByLatLng($lng1,$lat1,$lng2,$lat2){//根据经纬度计算距离 单位为米
        //将角度转为狐度
        $radLat1=deg2rad($lat1);
        $radLat2=deg2rad($lat2);
        $radLng1=deg2rad($lng1);
        $radLng2=deg2rad($lng2);
        $a=$radLat1-$radLat2;//两纬度之差,纬度<90
        $b=$radLng1-$radLng2;//两经度之差纬度<180
        $s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137*1000;
        return $s;
    }
}

if (!function_exists('distanceFormat')) {
    function distanceFormat($distance) {
        if (floatval($distance) <= 0) {
            return '0.1m';
        }
        if ($distance < 1000) {
            return $distance.'m';
        } else {
            return ($distance/1000).'km';
        }
    }
}

if (!function_exists('salaryFormat')) {
    function salaryFormat($salary,$format='k') {
        if (floatval($salary) < 1000) {
            return $salary;
        }
        return ($salary/1000).$format;
    }
}

if (!function_exists('formatCdnUrl')) {
    function formatCdnUrl($url) {
        if (config('app.env') == 'production') {
            $cdn_url = str_replace('http://inwehub-pro.oss-cn-zhangjiakou.aliyuncs.com','https://cdn.inwehub.com',$url);
            $format_url = parse_url($cdn_url);
            if (isset($format_url['host']) && !in_array($format_url['host'],['cdn.inwehub.com'])) {
                return false;
            }
            return $cdn_url;
        } else {
            return $url;
        }
    }
}

if (!function_exists('formatSlackUser')) {
    function formatSlackUser($user){
        return $user->id.'['.$user->name.']['.config('app.app_id').']';
    }
}



if (!function_exists('getContentUrls')) {
    function getContentUrls($content){
        preg_match_all('/(http|https):[\/]{2}[A-Za-z0-9,:\\._\\?#%&+\\-=\/()]*/',strip_tags(strip_html_tags(['a'],$content,true)),$urls);
        return $urls[0];
    }
}

if (!function_exists('formatContentUrls')) {
    function formatContentUrls($content){
        $urls = getContentUrls($content);
        if ($urls) {
            foreach ($urls as $url) {
                $info = getUrlInfo($url);
                if (empty($info['title'])) continue;
                $formatUrl = '['.$info['title'].']('.$url.')';
                $content = str_replace($url,$formatUrl,$content);
            }
        }
        return $content;
    }
}

if (!function_exists('strip_html_tags')) {
    /**
     * 删除指定的标签和内容
     * @param array  $tags 需要删除的标签数组
     * @param string $str 数据源
     * @param boole  $content 是否删除标签内的内容 默认为false保留内容  true不保留内容
     * @return string
     */
    function strip_html_tags($tags,$str,$content=false){
        $html=array();
        foreach ($tags as $tag) {
            if($content){
                $html[]='/(<'.$tag.'.*?>[\s|\S]*?<\/'.$tag.'>)/';
            }else{
                $html[]="/(<(?:\/".$tag."|".$tag.")[^>]*>)/i";
            }
        }
        $data=preg_replace($html, '', $str);
        return $data;
    }
}

if (!function_exists('formatAddressBookPhone')) {
    function formatAddressBookPhone($phone) {
        $phone = str_replace('+86','',$phone);
        $temp=array('1','2','3','4','5','6','7','8','9','0');
        $str = '';
        for($i=0;$i<strlen($phone);$i++) {
            if (in_array($phone[$i], $temp)) {
                $str .= $phone[$i];
            }
        }
        return $str;
    }
}

if (!function_exists('convertWechatLimitLinkToUnlimit')) {
    function convertWechatLimitLinkToUnlimit($link, $gzh_id) {
        $ch = curl_init();

        $url=urlencode($link);

        $account=urlencode($gzh_id);

        $url = "https://api.shenjian.io/?appid=46db4da70074ae0e7e08bc7ce90b8d50&url={$url}&account={$account}";

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Encoding:gzip'));

        curl_setopt($ch, CURLOPT_ENCODING, "gzip");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // 执行HTTP请求

        curl_setopt($ch , CURLOPT_URL , $url);

        $res = curl_exec($ch);

        curl_close($ch);

        return json_decode($res,true);
    }
}

if (!function_exists('convertWechatOvertimeLinkToUnlimit')) {
    function convertWechatOvertimeLinkToUnlimit($link) {
        $ch = curl_init();

        $url = "https://api.newrank.cn/api/async/task/sogou/advanced/towxurl";

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/x-www-form-urlencoded;charset=utf-8','Key:8ea425d1573648eabc57244a4'));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS,['url'=>urlencode($link)]);
        // 执行HTTP请求

        curl_setopt($ch , CURLOPT_URL , $url);

        $res = curl_exec($ch);

        curl_close($ch);

        return json_decode($res,true);
    }
}

if (!function_exists('queryWechatOvertimeLinkToUnlimit')) {
    function queryWechatOvertimeLinkToUnlimit($taskId) {
        $ch = curl_init();

        $url = "https://api.newrank.cn/api/task/result";

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/x-www-form-urlencoded;charset=utf-8','Key:8ea425d1573648eabc57244a4'));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS,['taskId'=>$taskId]);
        // 执行HTTP请求

        curl_setopt($ch , CURLOPT_URL , $url);

        $res = curl_exec($ch);

        curl_close($ch);

        return json_decode($res,true);
    }
}

if (!function_exists('getWechatArticleInfo')) {
    function getWechatArticleInfo($link) {
        $ch = curl_init();

        $url=urlencode($link);

        $url = "https://api.shenjian.io/?appid=25d11b844873dba7c0e2e205add34a27&url={$url}";

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Encoding:gzip'));

        curl_setopt($ch, CURLOPT_ENCODING, "gzip");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // 执行HTTP请求

        curl_setopt($ch , CURLOPT_URL , $url);

        $res = curl_exec($ch);

        curl_close($ch);
        return json_decode($res,true);
    }
}


if (!function_exists('getWechatUrlBodyText')) {
    function getWechatUrlBodyText($url,$strip_tags=true, $downloadImg = false) {
        $html = file_get_contents_curl($url);
        if (str_contains($html,'访问过于频繁，请用微信扫描二维码进行访问')) {
            $html = curlShadowsocks($url);
        }
        if (str_contains($html,'访问过于频繁，请用微信扫描二维码进行访问')) {
            $ql = \QL\QueryList::getInstance();
            $proxyIp = getPayProxyIp();
            $content = $ql->get($url,null,['proxy' => $proxyIp,'timeout'=>20]);
            $html = $content->getHtml();
            if (str_contains($html,'访问过于频繁，请用微信扫描二维码进行访问')) {
                event(new \App\Events\Frontend\System\ExceptionNotify('获取微信公众号文章内容失败：访问太频繁'));
            }
        }
        $parse = parse_url($url);
        if ($parse['host'] == 'mp.weixin.qq.com') {
            preg_match_all("/id=\"js_content\">(.*)<script/iUs",$html,$content,PREG_PATTERN_ORDER);
            $html = isset($content[1][0])?($strip_tags?strip_tags($content[1][0]):$content[1][0]):'';
            if ($downloadImg) {
                $html = preg_replace_callback('/data-src="(.*?)"/', function($matches){
                    $imgUrl = saveImgToCdn($matches[1],'wechat_temp');
                    return 'src="'.$imgUrl.'"';
                }, $html);
            }
            //去除微信图片遮罩
            $html = str_replace('opacity: 0;','',$html);
        }
        return $html;
    }
}

if (!function_exists('getWechatUrlInfo')) {
    function getWechatUrlInfo($url,$strip_tags=true, $downloadImg = false) {
        $ql = \QL\QueryList::getInstance();
        $headers = [
            'Host'    => 'mp.weixin.qq.com',
            'Origin'  => 'https://www.itjuzi.com',
            'Referer' => 'http://www.itjuzi.com/investevent',
            'Connection' => 'keep-alive',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
            'Upgrade-Insecure-Requests' => 1
        ];
        $content = $ql->get($url,null,['headers'=>$headers]);
        $html = $content->getHtml();
        if (str_contains($html,'访问过于频繁，请用微信扫描二维码进行访问')) {
            $res = \App\Services\Hmac\Client::instance()->request(config('app.partner_service_url').'/api/partner/service/fetWechatUrlInfo',['url'=>$url]);
            if ($res && $res['code'] == 1000) {
                return $res['data'];
            }
            throw new \App\Exceptions\ApiException(\App\Exceptions\ApiException::REQUEST_FAIL);
        }

        $parse = parse_url($url);
        if ($parse['host'] == 'mp.weixin.qq.com') {
            $title = $content->find('h2#activity-name')->text();
            $author = $content->find('a#js_name')->text();
            $wxHao = $content->find('span.profile_meta_value')->eq(0)->text();
            $pattern = "/var\s+ct\s+=\s+([\s\S]*?);/is";
            preg_match($pattern, $html, $matchs);
            $date = trim($matchs[1],'"');
            $pattern = "/var\s+msg_cdn_url\s+=\s+([\s\S]*?);/is";
            preg_match($pattern, $html, $matchs);
            $cover_img = trim($matchs[1],'"');

            preg_match_all("/id=\"js_content\">(.*)<script/iUs",$html,$body,PREG_PATTERN_ORDER);
            $js_content = isset($body[1][0])?($strip_tags?strip_tags($body[1][0]):$body[1][0]):'';
            if ($downloadImg) {
                $js_content = preg_replace_callback('/data-src="(.*?)"/', function($matches1){
                    $imgUrl = saveImgToCdn($matches1[1],'wechat_temp');
                    return 'src="'.$imgUrl.'"';
                }, $js_content);
            }
            //去除微信图片遮罩
            $js_content = str_replace('opacity: 0;','',$js_content);
            return [
                'body' => $js_content,
                'title' => $title,
                'author' => $author,
                'wxHao' => $wxHao,
                'date' => date('Y-m-d H:i:s',$date),
                'cover_img' => $cover_img
            ];
        }
        return $html;
    }
}

if (!function_exists('formatKeyword')) {
    function formatKeyword($keyword) {
        $keyword = trim($keyword);
        $keyword = str_replace('，','',$keyword);
        $keyword = str_replace('、','',$keyword);
        $keyword = str_replace('"','',$keyword);
        $keyword = str_replace('。','',$keyword);
        return $keyword;
    }
}

if (!function_exists('curlShadowsocks')) {
    function curlShadowsocks($url,$headers = []) {
        $ch = curl_init($url);
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0.3 Safari/605.1.15');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        //通过代理访问需要额外添加的参数项
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
        curl_setopt($ch, CURLOPT_PROXY, "127.0.0.1");
        curl_setopt($ch, CURLOPT_PROXYPORT, "1080");

        $result = curl_exec($ch);
        if($result === false){
            $error = curl_error($ch);
            var_dump($error);
            curl_close($ch);
            event(new \App\Events\Frontend\System\ExceptionNotify('curlShadowsocks error:'.$error));
            \App\Services\RateLimiter::instance()->setVale('curlShadowsocks','success',0,60*60*24);
            return false;
        }
        curl_close($ch);

        return $result;
    }
}


if (!function_exists('formatHtml')) {
    function formatHtml($html) {
        $html = str_replace('&#39;', '\'',$html);
        $html = str_replace('&amp;', '&',$html);
        $html = str_replace('&gt;', '>',$html);
        $html = str_replace('&lt;', '<',$html);
        $html = str_replace('&yen;', '¥',$html);
        $html = str_replace('amp;', '',$html);
        $html = str_replace('&lt;', '<',$html);
        $html = str_replace('&gt;', '>',$html);
        $html = str_replace('&nbsp;', ' ',$html);
        $html = str_replace('&quot;', '"',$html);
        $html = str_replace('\\', '',$html);
        return $html;
    }
}

if (!function_exists('checkInvalidTagString')) {
    function checkInvalidTagString($str) {
        return preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9_.\·\-\/ ]+$/u", $str);
    }
}






if (!function_exists('formatElasticSearchTitle')) {
    function formatElasticSearchTitle($title) {
        $cs = ['+','-','=','&&','||','>','<','!','(',')','{','}','[',']','^','"','~','*','?',':','\\','/'];
        foreach ($cs as $c) {
            $title = str_replace($c, "\\".$c,$title);
        }
        $title = str_replace(' ', "*",$title);
        return strtolower($title);
    }
}


if (!function_exists('imdbRank')) {
    /**
     * @param $average_rating ; 该项的投票平均分
     * @param $votes_number ;该项的投票人数
     * @param $minimum_votes ;总的最低投票数
     * @param $correctly_votes_rate ;总的平均分
     * @return float|int
     */
    function imdbRank($average_rating, $votes_number, $minimum_votes, $correctly_votes_rate) {
        return ($votes_number / ($votes_number + $minimum_votes)) * $average_rating + ($minimum_votes / (
                    $votes_number + $minimum_votes)) * $correctly_votes_rate;
    }
}


if (!function_exists('fiveStarCovertToUpAndDown')) {
    function fiveStarCovertToUpAndDown($oneNum,$twoNum,$threeNum,$fourNum,$fiveNum) {
        $upvotes = $oneNum * 0 + $twoNum * 0.25 + $threeNum * 0.5 + $fourNum * 0.75 + $fiveNum * 1;
        $downvotes = $oneNum * 1 + $twoNum * 0.75 + $threeNum * 0.5 + $fourNum * 0.25 + $fiveNum * 0;
        return [
            'up' => $upvotes,
            'down' => $downvotes
        ];
    }
}



function varianceCalc($arr) {
    $length = count($arr);
    if ($length == 0) {
        return array(0,0);
    }
    $average = array_sum($arr)/$length;
    $count = 0;
    foreach ($arr as $v) {
        $count += pow($average-$v, 2);
    }
    $variance = $count/$length;
    return array('variance' => $variance, 'square' => sqrt($variance), 'average' => $average);
}

/**
 * 自动识别关键词方法
 * @param String $text 需要查询的文本
 * @param Array $keysStr 用来标记的关键词字符串
 * @param Int $similar 可以插入的关键词相似度 默认60%
 * @return Array
 */
function searchKeys($text,$keysArr_1D,$similar = ""){
//关键词相似度
    $similar = $similar == null ? 60 : $similar;
//组装特殊字符，并替换
    //$Exp = str_replace(array(":","。",'"',"/","-","_","=","~","`","(",")","*","&","^","%","$","#","@","!",":","：","、","“","．","”",";","】","【","[","]","|",'\/'," ","　","＇","＂","＜","＞","?","／","］","［","！","＠","＃","＄","％","＾","＆","＊","（","）","＿","＋","＝","－","／","＊","－","＋","．","｀","～","；","：","＇","＂","｜","＼"),"",strip_tags($text));
    $Exp = str_replace(array(":","。",'"',"_","=","~","`","*","&","^","%","$","#","@","!",":","：","、","“","．","”",";","】","【","[","]","|",'\/',"＇","＂","＜","＞","?","／","］","［","！","＠","＃","＄","％","＾","＆","＊","（","）","＿","＋","＝","－","／","＊","－","．","｀","～","；","：","＇","＂","｜","＼"),"",strip_tags($text));
    $Exps = str_replace("，",",",$Exp);

//将切割的文字组装成数组
    $textArr_1D = explode(",",$Exps);
//将内容转换成二维数组
    $textArr_2D = array();
    foreach($textArr_1D as $val){
        $textArr_2D[]['text'] = $val;
    }
    $data = [];
//切割关键词成一维数组
//转换成二维数组
    $keysArr_2D = array();
    foreach ($keysArr_1D as $val) {
        //长度小于4的过滤掉
        if (strlen($val) <= 4) continue;
        $keysArr_2D[]['keys'] = $val;
    }
//开始匹配关键词
    foreach ($textArr_2D as $t_k => $t_v) {
        foreach ($keysArr_2D as $k_k => $k_v) {
//判断关键词不为空
            if($k_v['keys'] != ""){
//根据文本相似度
                if(similar_text($t_v['text'],$k_v['keys'],$percent)){
//当相似度大于等于**时插入到数组
                    if($percent >= $similar){
                        $data[]['keys'] = $k_v['keys'].$percent."%";
                    }
                }
//不区分大小写寻找相同字符
                if(stristr($t_v['text'],$k_v['keys']) != false){
                    $data[]['keys'] = $k_v['keys'];
                }
//区分大小写寻找相同字符
                if(strpos($t_v['text'],$k_v['keys']) != false){
                    $data[]['keys'] = $k_v['keys'];
                }
            }
        }
    }
    return array_unique_fb($data);
}
/**
 * 数组去重方法
 */
function array_unique_fb($array2D){
    $temp = [];
    foreach ($array2D as $v){
        $v = join(",",$v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
        $temp[] = $v;
    }
    $temp = array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
    foreach ($temp as $k => $v){
        $temp[$k] = explode(",",$v); //再将拆开的数组重新组装
    }
    return $temp;
}

/*
 *
 * @desc URL安全形式的base64编码
 * @param string $str
 * @return string
 */
function urlsafe_base64_encode($str){
    $find = array("+","/");
    $replace = array("-", "_");
    return str_replace($find, $replace, base64_encode($str));
}


function weapp_qrcode_replace_logo($qrcodeUrl,$newLogoUrl,$circleQr = false) {
    $circleLogo = \App\Services\RateLimiter::instance()->hGet('weapp_dp_logo_circle',$newLogoUrl);
    if (!$circleLogo) {
        $circleLogo = $newLogoUrl.'?x-oss-process=image/resize,m_lfit,h_192,w_192,limit_0,image/circle,r_100/format,png';
        $file_name = 'product/qrcode/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.png';
        Storage::disk('oss')->put($file_name,file_get_contents($circleLogo));
        $circleLogo = Storage::disk('oss')->url($file_name);
        \App\Services\RateLimiter::instance()->hSet('weapp_dp_logo_circle',$newLogoUrl,$circleLogo);
    }
    $logoUrl = str_replace('https://cdn.inwehub.com/','',$circleLogo);

    $s = urlsafe_base64_encode($logoUrl);
    return $qrcodeUrl.'?x-oss-process=image/resize,w_430,h_430'.($circleQr?',image/circle,r_300/format,png':'').'/watermark,image_'.$s.',g_center';
}



