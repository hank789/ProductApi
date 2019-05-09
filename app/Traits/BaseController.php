<?php namespace App\Traits;
/**
 * @author: wanghui
 * @date: 2017/4/7 下午1:32
 * @email: hank.huiwang@gmail.com
 */
use App\Exceptions\ApiException;
use App\Jobs\NewSubmissionJob;
use App\Jobs\UploadFile;
use App\Models\Category;
use App\Models\Comment;
use App\Models\DownVote;
use App\Models\Submission;
use App\Models\Support;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\Taggable;
use App\Models\User;
use App\Models\UserTag;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Zhuzhichao\IpLocationZh\Ip;
use Illuminate\Http\Request;

trait BaseController {

    protected function findIp($ip): array
    {
        return (array) Ip::find($ip);
    }


    protected function checkCommentIsSupported($user_id, &$comment) {
        $support = Support::where("user_id",'=',$user_id)->where('supportable_type','=',Comment::class)->where('supportable_id','=',$comment['id'])->first();
        $comment['is_supported'] = $support?1:0;
        if ($comment['children']) {
            foreach ($comment['children'] as &$children) {
                $this->checkCommentIsSupported($user_id, $children);
            }
        } else {
            return;
        }
    }


    protected function uploadImgs($photos,$dir='submissions'){
        $list = [];
        if ($photos) {
            if (!is_array($photos)) $photos = [$photos];
            foreach ($photos as $base64) {
                $url = explode(';',$base64);
                if(count($url) <=1){
                    $parse_url = parse_url($base64);
                    //非本地地址，存储到本地
                    if (isset($parse_url['host']) && !in_array($parse_url['host'],['cdnread.ywhub.com','cdn.inwehub.com','inwehub-pro.oss-cn-zhangjiakou.aliyuncs.com','intervapp-test.oss-cn-zhangjiakou.aliyuncs.com'])) {
                        $file_name = $dir.'/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.jpeg';
                        dispatch((new UploadFile($file_name,base64_encode(file_get_contents_curl($base64)))));
                        //Storage::disk('oss')->put($file_name,file_get_contents($base64));
                        $img_url = Storage::disk('oss')->url($file_name);
                        $list[] = $img_url;
                    } elseif(isset($parse_url['host'])) {
                        $list[] = $base64;
                    }
                    continue;
                }
                $url_type = explode('/',$url[0]);
                $file_name = $dir.'/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$url_type[1];
                dispatch((new UploadFile($file_name,(substr($url[1],6)))));
                //Storage::disk('oss')->put($file_name,base64_decode(substr($url[1],6)));
                $img_url = Storage::disk('oss')->url($file_name);
                $list[] = $img_url;
            }
        }
        return ['img'=>$list];
    }

    protected function uploadFile($files,$dir='submissions'){
        $list = [];
        if ($files) {
            foreach ($files as $file) {
                $url = explode(';',$file['base64']);
                if(count($url) <=1){
                    continue;
                }
                $url_type = explode('/',$url[0]);
                $file_name = $dir.'/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$url_type[1];
                dispatch((new UploadFile($file_name,(substr($url[1],6)))));
                $img_url = Storage::disk('oss')->url($file_name);
                $list[] = [
                    'name' => $file['name'],
                    'type' => $url_type[1],
                    'url' =>$img_url
                ];
            }
        }
        return $list;
    }

    protected function getTagProductInfo(Tag $tag) {
        $reviewInfo = Tag::getReviewInfo($tag->id);
        $data = $tag->toArray();
        $data['review_count'] = $reviewInfo['review_count'];
        $data['review_average_rate'] = $reviewInfo['review_average_rate'];
        $submissions = Submission::selectRaw('count(*) as total,rate_star')->where('status',1)->where('category_id',$tag->id)->groupBy('rate_star')->get();
        foreach ($submissions as $submission) {
            $data['review_rate_info'][] = [
                'rate_star' => $submission->rate_star,
                'count'=> $submission->total
            ];
        }

        $data['related_tags'] = $tag->relationReviews(4);
        $categoryRels = TagCategoryRel::where('tag_id',$tag->id)->where('type',TagCategoryRel::TYPE_REVIEW)->orderBy('review_average_rate','desc')->get();
        $cids = [];
        foreach ($categoryRels as $key=>$categoryRel) {
            $cids[] = $categoryRel->category_id;
            $category = Category::find($categoryRel->category_id);
            $rate = TagCategoryRel::where('category_id',$category->id)->where('review_average_rate','>',$categoryRel->review_average_rate)->count();
            $data['categories'][] = [
                'id' => $category->id,
                'name' => $category->name,
                'rate' => $rate+1,
                'support_rate' => $categoryRel->support_rate?:0,
                'type' => $category->type == 'enterprise_review'?1:2
            ];
        }
        $data['vendor'] = '';
        $taggable = Taggable::where('tag_id',$tag->id)->where('taggable_type',CompanyData::class)->first();
        if ($taggable) {
            $companyData = CompanyData::find($taggable->taggable_id);
            $data['vendor'] = [
                'id'=>$taggable->taggable_id,
                'name'=>$companyData->name
            ];
        }
        //推荐股问
        /*$releatedTags = TagCategoryRel::whereIn('category_id',$cids)->pluck('tag_id')->toArray();
        $recommendUsers = UserTag::whereIn('tag_id',$releatedTags)->where('user_id','!=',$user->id)->orderBy('skills','desc')->take(5)->get();
        $skillTags = TagsLogic::loadTags(5,'')['tags'];
        foreach ($recommendUsers as $recommendUser) {
            $userTags = UserTag::where('user_id',$recommendUser->user_id)->whereIn('tag_id',array_column($skillTags,'value'))->orderBy('skills','desc')->pluck('tag_id');
            if (!isset($userTags[0])) continue;
            $skillTag = Tag::find($userTags[0]);
            if (!$skillTag) continue;
            $data['recommend_users'][] = [
                'name' => $recommendUser->user->name,
                'id'   => $recommendUser->user_id,
                'uuid' => $recommendUser->user->uuid,
                'is_expert' => $recommendUser->user->is_expert,
                'avatar_url' => $recommendUser->user->avatar,
                'skill' => $skillTag->name
            ];
        }*/
        return $data;
    }

    protected function formatSubmissionInfo(Request $request,Submission $submission, $user) {
        $return = $submission->toArray();

        $upvote = Support::where('user_id',$user->id)
            ->where('supportable_id',$submission->id)
            ->where('supportable_type',Submission::class)
            ->exists();
        $downvote = DownVote::where('user_id',$user->id)
            ->where('source_id',$submission->id)
            ->where('source_type',Submission::class)
            ->exists();


        $return['is_followed_author'] = 0;
        $return['is_upvoted'] = $upvote ? 1 : 0;
        $return['is_downvoted'] = $downvote ? 1 : 0;
        $return['is_bookmark'] = 0;
        $return['supporter_list'] = [];
        $return['support_description'] = $downvote?$submission->getDownvoteRateDesc():$submission->getSupportRateDesc($upvote);
        $return += $submission->getSupportTypeTip();
        $return['support_percent'] = $submission->getSupportPercent();
        $return['tags'] = $submission->tags()->wherePivot('is_display',1)->get()->toArray();
        foreach ($return['tags'] as $key=>$tag) {
            $return['tags'][$key]['review_average_rate'] = 0;
            if (isset($submission->data['category_ids'])) {
                $reviewInfo = Tag::getReviewInfo($tag['id']);
                $return['tags'][$key]['reviews'] = $reviewInfo['review_count'];
                $return['tags'][$key]['review_average_rate'] = $reviewInfo['review_average_rate'];
            }
        }
        $return['is_commented'] = $submission->comments()->where('user_id',$user->id)->exists() ? 1: 0;
        $return['bookmarks'] = 0;
        $return['data']['current_address_name'] = $return['data']['current_address_name']??'';
        $return['data']['current_address_longitude'] = $return['data']['current_address_longitude']??'';
        $return['data']['current_address_latitude']  = $return['data']['current_address_latitude']??'';
        $img = $return['data']['img']??'';
        if (false && in_array($return['group']['is_joined'],[-1,0,2]) && $img) {
            if (is_array($img)) {
                foreach ($img as &$item) {
                    $item .= '?x-oss-process=image/blur,r_20,s_20';
                }
            } else {
                $img .= '?x-oss-process=image/blur,r_20,s_20';
            }
        }
        $return['data']['img'] = $img;
        $return['related_question'] = null;


        if ($submission->hide) {
            //匿名
            $return['owner']['avatar'] = config('image.user_default_avatar');
            $return['owner']['name'] = '匿名';
            $return['owner']['id'] = '';
            $return['owner']['uuid'] = '';
            $return['owner']['is_expert'] = 0;
        }
        $return['related_tags'] = $submission->getRelatedProducts();
        //seo信息
        $keywords = array_unique(explode(',',$submission->data['keywords']??''));
        $return['seo'] = [
            'title' => strip_tags($submission->type == 'link' ? $submission->data['title'] : $submission->title),
            'description' => strip_tags($submission->title),
            'keywords' => implode(',',array_slice($keywords,0,5)),
            'published_time' => (new Carbon($submission->created_at))->toAtomString()
        ];
        return $return;
    }

    protected function storeSubmission(Request $request,$user) {
        $user_id = $user->id;
        if (RateLimiter::instance()->increase('submission:store',$user_id,5)) {
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        if ($request->type == 'link') {
            $category = Category::where('slug','channel_xwdt')->first();
        } else {
            $category = Category::where('slug','channel_gddj')->first();
        }
        $category_id = $category->id;

        $tagString = $request->input('tags');
        $newTagString = $request->input('new_tags');
        if ($newTagString) {
            if (is_array($newTagString)) {
                foreach ($newTagString as $s) {
                    if (strlen($s) > 46) throw new ApiException(ApiException::TAGS_NAME_LENGTH_LIMIT);
                }
            } else {
                if (strlen($newTagString) > 46) throw new ApiException(ApiException::TAGS_NAME_LENGTH_LIMIT);
            }
        }
        $group_id = $request->input('group_id',0);
        $public = 1;
        $hide = $request->input('hide',0);


        //点评
        if ($request->type == 'review') {
            $this->validate($request, [
                'title' => 'required|between:1,6000',
                'tags' => 'required',
                'rate_star' => 'required|min:1',
                'identity' => 'required'
            ]);
            $data = $this->uploadImgs($request->input('photos'));
            $data['category_ids'] = $request->input('category_ids',[]);
            $data['author_identity'] = $request->input('identity');
            $data['from_source'] = $request->input('inwehub_user_device');
            if (!is_array($data['author_identity'])) {
                $data['author_identity'] = [$data['author_identity']];
            }

            $category_id = $tagString;
        }

        if ($request->input('files')) {
            $data['files'] = $this->uploadFile($request->input('files'));
        }

        try {
            $data['current_address_name'] = $request->input('current_address_name');
            $data['current_address_longitude'] = $request->input('current_address_longitude');
            $data['current_address_latitude'] = $request->input('current_address_latitude');
            $data['mentions'] = is_array($request->input('mentions'))?array_unique($request->input('mentions')):[];
            $title = formatHtml($request->title);
            $submission = Submission::create([
                'title'         => formatContentUrls($title),
                'slug'          => $this->slug($title),
                'type'          => $request->type,
                'category_id'   => $category_id,
                'group_id'      => $group_id,
                'public'        => $public,
                'rate'          => firstRate(),
                'rate_star'     => $request->input('rate_star',0),
                'hide'          => $hide,
                'status'        => $request->input('draft',0)?0:1,
                'user_id'       => $user_id,
                'data'          => $data,
                'views'         => 1
            ]);
            if ($request->type == 'link') {
                Redis::connection()->hset('voten:submission:url',$request->url, $submission->id);
            }

            /*添加标签*/
            Tag::multiSaveByIds($tagString,$submission);
            if ($newTagString) {
                Tag::multiAddByName($newTagString,$submission);
            }
            UserTag::multiIncrement($user_id,$submission->tags()->get(),'articles');
            if ($request->input('identity') && $request->input('identity') != -1) {
                UserTag::multiIncrement($user_id,[Tag::find($request->input('identity'))],'role');
            }
            if ($submission->status == 1) {
                $this->dispatch((new NewSubmissionJob($submission->id,false,$request->input('inwehub_user_device')== 'weapp_dianping'?'小程序':'')));
            }

        } catch (\Exception $exception) {
            app('sentry')->captureException($exception);
            throw new ApiException(ApiException::ERROR);
        }
        self::$needRefresh = true;
        return self::createJsonData(true,$submission->toArray());
    }

    protected function tagProductList(Request $request) {
        $category_id = $request->input('category_id',0);
        $orderBy = $request->input('orderBy',1);
        $page = $request->input('page',1);
        $cacheKey = 'tags:product_list_'.$category_id.'_'.$orderBy.'_'.$page;
        $preCacheKey = '';
        if ($page > 1) {
            $preCacheKey = 'tags:product_list_'.$category_id.'_'.$orderBy.'_'.($page-1);
        }
        $return = Cache::get($cacheKey);
        if (!$return) {
            $query = TagCategoryRel::select(['tag_id'])->where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1);
            if ($category_id) {
                $category = Category::find($category_id);
                if ($category->grade == 1) {
                    $children = Category::getChildrenIds($category_id);
                    $children[] = $category_id;
                    $query = $query->whereIn('category_id',array_unique($children));
                } else {
                    $query = $query->where('category_id',$category_id);
                }
            }
            switch ($orderBy) {
                case 1:
                    $query = $query->orderBy('review_average_rate','desc');
                    break;
                case 2:
                    $query = $query->orderBy('reviews','desc');
                    break;
                default:
                    $query = $query->orderBy('updated_at','desc');
                    break;
            }
            $tags = $query->distinct()->groupBy('tag_id')->simplePaginate(30);
            $return = $tags->toArray();
            $list = [];
            $used = [];
            $preCache = $preCacheKey?Cache::get($preCacheKey):'';
            if ($preCache) {
                $used = array_column($preCache['data'],'id');
            }
            foreach ($tags as $tag) {
                if (in_array($tag->tag_id, $used)) continue;
                $model = Tag::find($tag->tag_id);
                $info = Tag::getReviewInfo($model->id);
                $used[$tag->tag_id] = $tag->tag_id;
                $list[] = [
                    'id' => $model->id,
                    'name' => $model->name,
                    'logo' => $model->logo,
                    'review_count' => $info['review_count'],
                    'review_average_rate' => $info['review_average_rate']
                ];
            }
            $return['data'] = $list;
            Cache::forever($cacheKey,$return);
        }
        return $return;
    }

}