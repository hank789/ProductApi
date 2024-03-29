<?php

namespace App\Models;
use App\Exceptions\ApiException;
use App\Models\Groups\GroupMember;
use App\Models\IM\Message;
use App\Models\Pay\UserMoney;
use App\Models\Relations\HasRoleAndPermission;
use App\Models\Relations\MorphManyTagsTrait;
use App\Services\NotificationSettings;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $mobile
 * @property string $password
 * @property bool $gender
 * @property string $birthday
 * @property int $province
 * @property int $city
 * @property string $title
 * @property string $description
 * @property bool $status
 * @property string $site_notifications
 * @property string $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Answer[] $answers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Article[] $articles
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Attention[] $attentions
 * @property-read \App\Models\Authentication $authentication
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Collection[] $collections
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Credit[] $credits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Doing[] $doings
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Exchange[] $exchanges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserData[] $followers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionInvitation[] $questionInvitations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Question[] $questions
 * @property-read \Illuminate\Database\Eloquent\Collection|\Bican\Roles\Models\Role[] $roles
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read \App\Models\UserData $userData
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserOauth[] $userOauth
 * @property-read \Illuminate\Database\Eloquent\Collection|\Bican\Roles\Models\Permission[] $userPermissions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserTag[] $userTag
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserTag[] $userTags
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereBirthday($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereCity($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereEmailNotifications($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereGender($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereMobile($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereProvince($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereSiteNotifications($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $uuid
 * @property string|null $avatar
 * @property string|null $hometown_province
 * @property string|null $hometown_city
 * @property string $address_detail
 * @property string $company
 * @property int $source 注册来源
 * @property string|null $last_login_token 上次登录token
 * @property string $current_app_version
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Company\Project[] $companyProjects
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\IM\Message[] $conversations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserInfo\EduInfo[] $edus
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserInfo\JobInfo[] $jobs
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LoginRecord[] $loginRecords
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\Media[] $media
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\IM\Message[] $messages
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pay\MoneyLog[] $moneyLogs
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserInfo\ProjectInfo[] $projects
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Task[] $tasks
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserInfo\TrainInfo[] $trains
 * @property-read \App\Models\Company\Company $userCompany
 * @property-read \App\Models\Pay\UserMoney $userMoney
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereAddressDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCurrentAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereHometownCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereHometownProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLastLoginToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUuid($value)
 * @property string|null $rc_code
 * @property int|null $rc_uid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereRcCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereRcUid($value)
 * @property int $submission_karma
 * @property int $comment_karma
 * @property int $is_expert
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCommentKarma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereIsExpert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereSubmissionKarma($value)
 * @property string|null $email_notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Submission[] $submissions
 * @property int $info_complete_percent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereInfoCompletePercent($value)
 */
class User extends Model implements AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract,
    HasMedia
{
    use Notifiable, Authenticatable, CanResetPassword,HasRoleAndPermission,MorphManyTagsTrait,HasMediaTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','realname','uuid','rc_code','rc_uid','mobile' ,'info_complete_percent','is_expert','avatar','email','title','company', 'password','status','site_notifications','last_login_token','source'];

    protected $casts = [
        'site_notifications' => 'json',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];


    const USER_SOURCE_APP = 0;//用户来源:app注册
    const USER_SOURCE_WEAPP = 1;//用户来源:微信小程序自动注册
    const USER_SOURCE_WEIXIN_GZH = 2;//用户来源:微信公众号
    const USER_SOURCE_WEAPP_ASK = 3;//用户来源:微信小程序-推荐自动注册
    const USER_SOURCE_WEB = 4;//用户来源:网站
    const USER_SOURCE_DOUBAN = 5;//用户来源:豆瓣抓取
    const USER_SOURCE_WEAPP_DB = 6;//用户来源:微信小程序-点评

    public static function boot()
    {
        parent::boot();
        static::deleted(function($user){
            UserData::where('user_id',$user->id)->delete();
        });
    }

    public static function getAvatarPath($userId,$size='big',$ext='jpg')
    {
        $avatarDir = self::getAvatarDir($userId);
        $avatarFileName = self::getAvatarFileName($userId,$size);
        return $avatarDir.'/'.$avatarFileName.'.'.$ext;
    }

    /**
     * 获取用户头像存储目录
     * @param $user_id
     * @return string
     */
    public static function getAvatarDir($userId,$rootPath='avatars')
    {
        $userId = sprintf("%09d", $userId);
        return $rootPath.'/'.substr($userId, 0, 3) . '/' . substr($userId, 3, 2) . '/' . substr($userId, 5, 2);
    }


    /**
     * 获取头像文件命名
     * @param string $size
     * @return mixed
     */
    public static function getAvatarFileName($userId,$size='big')
    {
        $avatarNames = [
            'small'=>'user_small_'.$userId,
            'middle'=>'user_middle_'.$userId,
            'big'=>'user_big_'.$userId,
            'origin'=>'user_origin_'.$userId
        ];
       return $avatarNames[$size];
    }


    /**
     * 从缓存中获取用户数据，主要用户问答文章等用户数据显示
     * @param $userId
     * @return mixed
     */
    public static function findFromCache($userId)
    {

        $data = Cache::remember('user_cache_'.$userId,Config::get('inwehub.user_cache_time'),function() use($userId) {
            return  self::select('name','title','gender')->find($userId);
        });

        return $data;
    }

    /*搜索*/
    public static function search($word)
    {
        $list = self::where('name','like',"%$word%");
        return $list;
    }

    public function getRegisterSource(){
        switch($this->source){
            case self::USER_SOURCE_APP:
                return 'APP';
            case self::USER_SOURCE_WEIXIN_GZH:
                return '微信公众号';
            case self::USER_SOURCE_WEAPP:
                return '项目招募助手';
            case self::USER_SOURCE_WEAPP_ASK:
                return '精选推荐';
            case self::USER_SOURCE_WEB:
                return '官网';
            case self::USER_SOURCE_WEAPP_DB:
                return '企业服务点评';
        }
        return 'APP';
    }

    public function getInviter(){
        if ($this->rc_uid) {
            return User::find($this->rc_uid);
        }
        return null;
    }

    public function getInvitedUserCount(){
        return self::where('rc_uid',$this->id)->count();
    }

    /**
     * 用户登录记录关系.
     *
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function loginRecords()
    {
        return $this->hasMany(loginRecord::class, 'user_id');
    }

    /**
     * @return NotificationSettings
     */
    public function notificationSettings()
    {
        return new NotificationSettings(is_array($this->site_notifications)?$this->site_notifications:[], $this);
    }

    /**
     *获取用户数据
     * @param $userId
     */
    public function userData()
    {
        return $this->hasOne('App\Models\UserData');
    }

    public function userMoney()
    {
        return $this->hasOne('App\Models\Pay\UserMoney');
    }

    public function userTag(){
        return $this->hasMany('App\Models\UserTag');
    }

    public function userSkillTag(){
        return $this->userTag()->where('skills','>',0);
    }

    public function userRegionTag(){
        return $this->userTag()->where('region','>',0);
    }

    public function userOauth(){
        return $this->hasMany('App\Models\UserOauth');
    }


    /*用户认证信息*/
    public function authentication()
    {
        return $this->hasOne('App\Models\Authentication');
    }

    /*用户认证信息*/
    public function userCompany()
    {
        return $this->hasOne('App\Models\Company\Company');
    }

    /**
     * 获取用户问题
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany('App\Models\Question');
    }

    /**
     * 获取用户回答
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany('App\Models\Answer');
    }

    public function submissions()
    {
        return $this->hasMany('App\Models\Submission');
    }

    public function tasks(){
        return $this->hasMany('App\Models\Task');
    }

    public function companyProjects(){
        return $this->hasMany('App\Models\Company\Project');
    }


    /**
     * 获取用户文章
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function articles()
    {
        return $this->hasMany('App\Models\Article');
    }

    /**
     * 获取用户动态
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function doings()
    {
        return $this->hasMany('App\Models\Doing');
    }


    /*我的评论*/

    public function comments(){
        return $this->hasMany('App\Models\Comment');

    }


    /*我的积分操作*/
    public function credits(){
        return $this->hasMany('App\Models\Credit');

    }


    /*获取用户收藏*/
    public function collections()
    {
        return $this->hasMany('App\Models\Collection');
    }


    /*用户关注*/
    public function attentions()
    {
        return $this->hasMany('App\Models\Attention');
    }

    /*用户粉丝*/
    public function followers()
    {
        return $this->morphToMany('App\Models\UserData', 'source','attentions','source_id','user_id');
    }

    /*邀请的回答*/
    public function questionInvitations()
    {
        return $this->hasMany('App\Models\QuestionInvitation');
    }

    /*我的商品兑换*/
    public function exchanges()
    {
        return $this->hasMany('App\Models\Exchange');
    }

    /*用户统计标签*/
    public function userTags(){
        return $this->hasMany('App\Models\UserTag','user_id');
    }

    //工作经历
    public function jobs(){
        return $this->hasMany('App\Models\UserInfo\JobInfo','user_id');
    }

    public function getWorkYears(){
        $begin = $this->jobs()->orderBy('begin_time','asc')->first();
        if($begin){
            $begin_time = new Carbon($begin->begin_time);
            $end_time = new Carbon(date('Y-m'));
            return $end_time->diffInYears($begin_time);
        }
        return '';
    }

    //教育经历
    public function edus(){
        return $this->hasMany('App\Models\UserInfo\EduInfo','user_id');
    }

    //培训经历
    public function trains(){
        return $this->hasMany('App\Models\UserInfo\TrainInfo','user_id');
    }

    //项目经历
    public function projects(){
        return $this->hasMany('App\Models\UserInfo\ProjectInfo','user_id');
    }

    //资金明细
    public function moneyLogs(){
        return $this->hasMany('App\Models\Pay\MoneyLog','user_id');
    }

    //IM会话
    public function conversations()
    {
        return $this->belongsToMany(Message::class, 'im_conversations')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function hotTags(){
        $hotTagIds = $this->userTags()->select("tag_id")->distinct()->orderBy('supports','desc')->orderBy('answers','desc')->orderBy('created_at','desc')->take(5)->pluck('tag_id');
        $tags = [];
        foreach($hotTagIds as $hotTagId){
            $tag = Tag::find($hotTagId);
            if($tag){
                $tags[] = $tag;
            }

        }
        return $tags;
    }

    public function industryTags(){
        $tagIds = $this->userTags()->select("tag_id")->distinct()->where('industries','>',0)->orderBy('created_at','desc')->get()->pluck('tag_id');
        $tags = [];
        foreach($tagIds as $tagId){
            $tag = Tag::find($tagId);
            if($tag){
                $tags[] = $tag;
            }
        }
        return $tags;
    }

    public function skillTags(){
        $tagIds = $this->userTags()->select("tag_id")->distinct()->where('skills','>',0)->orderBy('created_at','desc')->get()->pluck('tag_id');
        $tags = [];
        foreach($tagIds as $tagId){
            $tag = Tag::find($tagId);
            if($tag){
                $tags[] = $tag;
            }
        }
        return $tags;
    }

    //获得用户头像地址
    public function getAvatarUrl(){
        if($this->getMedia('avatar')->isEmpty()){
            if ($this->userOauth->count() && $this->userOauth->last()->avatar) {
                return $this->userOauth->last()->avatar;
            }
            return config('image.user_default_avatar');
        }else
            return $this->getMedia('avatar')->last()->getUrl();
    }

    //获得用户简历地址
    public function getResumeMedias(){
        if($this->getMedia('resume')->isEmpty()){
            return [];
        }else{
            return $this->getMedia('resume');
        }
    }





    /*是否回答过问题*/
    public function isAnswered($questionId)
    {
        return boolval($this->answers()->where('question_id','=',$questionId)->count());
    }


    /*是否已经收藏过问题或文章*/
    public function isCollected($source_type,$source_id)
    {
        return $this->collections()->where('source_type','=',$source_type)->where('source_id','=',$source_id)->first();
    }



    /*是否已关注问题、用户*/
    public function isFollowed($source_type,$source_id)
    {
        return boolval($this->attentions()->where('source_type','=',$source_type)->where('source_id','=',$source_id)->count());
    }


    /**
     * 第三方账号是否绑定
     * @param $auth_type
     * @return bool
     */
    public function isOauthBind($auth_type){
        if($this->userOauth()->where("auth_type",'=',$auth_type)->count()){
            return true;
        }
        return false;
    }

    /*判断用户是否开启了邮件通知*/
    public function allowedEmailNotify($type){
        if(!in_array($type,explode(",",$this->email_notifications))){
            return false;
        }
        return true;
    }

    //获取信息完整度百分比
    public function getInfoCompletePercent($include_unfilled_fields = false){
        try{
            $user = $this->toArray();
            $info = [];
            $info['name'] = [5=>$user['name']];
            $info['mobile'] = [5=>$user['mobile']];
            $info['email'] = [5=>$user['email']];
            $info['gender'] = [5=>$user['gender']];
            $info['birthday'] = [5=>$user['birthday']];
            $info['city'] = [5=>$user['city']];
            $info['hometown_city'] = [1=>$user['hometown_city']];
            $info['company'] = [5=>$user['company']];
            $info['title'] = [5=>$user['title']];
            $info['description'] = [1=>$user['description']];
            $info['address_detail'] = [5=>$user['address_detail']];
            $info['industry_tags'] = [5=>array_column($this->industryTags(),'name')];
            $info['avatar_url'] = [10=>$this->getAvatarUrl()];

            $edu = [10=>$this->edus()->pluck('id')];
            $job = [10=>$this->jobs()->pluck('id')];
            $project = [10=>$this->projects()->pluck('id')];
            $train = [2=>$this->trains()->pluck('id')];
            $data = [];
            $data['info'] = $info;
            $data['jobs'] = $job;
            $data['edus'] = $edu;
            $data['projects'] = $project;
            $data['trains'] = $train;

            $fields = cal_account_info_finish($data);
            if ($include_unfilled_fields) {
                return $fields;
            }
            return $fields['score'];
        }catch (\Exception $e) {
            return 0;
        }
    }

    public static function getFieldHumanName($field){
        $name = '';
        switch($field){
            case 'name':
                $name = '姓名';
                break;
            case 'email':
                $name = '邮箱';
                break;
            case 'mobile':
                $name = '手机号';
                break;
            case 'gender':
                $name = '性别';
                break;
            case 'birthday':
                $name = '生日';
                break;
            case 'city':
            case 'province':
                $name = '工作城市';
                break;
            case 'hometown_city':
            case 'hometown_province':
                $name = '家乡城市';
                break;
            case 'address_detail':
                $name = '详细地址';
                break;
            case 'company':
                $name = '所在公司';
                break;
            case 'title':
                $name = '当前职位';
                break;
            case 'description':
                $name = '个人签名';
                break;
            case 'avatar_url':
                $name = '头像';
                break;
            case 'industry_tags':
                $name = '所在行业';
                break;
        }
        return $name;
    }

    public function getUserLevel($credits = 0){
        if ($credits == 0) {
            $credits = $this->userData->credits;
        }

        $level = 1;
        switch(true){
            case $credits <= 500 :
                $level = 1;
                break;
            case $credits <= 5000:
                $level = 2;
                break;
            case $credits <= 50000:
                $level = 3;
                break;
            case $credits <= 500000:
                $level = 4;
                break;
            default:
                $level = 5;
                break;
        }
        return $level;
    }

    public function getLockMoney(){
        $s = RateLimiter::instance()->getValue('user:lock_money',$this->id);
        return $s ? : 0;
    }

    public function lockMoney($money,$seconds=120) {
        return RateLimiter::instance()->increaseBy('user:lock_money',$this->id,ceil($money),$seconds);
    }

    //获取用户可用金额
    public function getAvailableTotalMoney(){
        if (in_array($this->id,[504])) return 0;
        $user_total_money = $this->userMoney->total_money;
        $lockMoney = $this->getLockMoney();
        $user_total_money -= $lockMoney;
        return $user_total_money;
    }

    public function checkCanDisturbNotify(){
        $disturb = $this->site_notifications['push_do_not_disturb']??0;
        if ($disturb && (time()>=strtotime(date('Y-m-d 22:00:00')) || time()<=strtotime(date('Y-m-d 07:30:00'))))
        {
            return false;
        }
        return true;
    }

    //合并用户
    public function mergeUser(User $fromUser) {
        if ($this->id == $fromUser->id) return;
        $user = $this;
        //如果有结算中的余额，暂时不处理
        $remain_money = $fromUser->userMoney->settlement_money+$fromUser->userMoney->total_money;
        if ($remain_money>0) {
            throw new ApiException(ApiException::USER_HAS_MONEY_REMAIN);
        }
        //合并微信账户
        //1.当前用户的微信登陆信息都改为手机号用户的id
        UserOauth::where('user_id',$fromUser->id)->update(['user_id'=>$user->id]);
        //2.当前用户加入的圈子
        $groupIds = GroupMember::where('user_id',$user->id)->pluck('group_id')->toArray();
        GroupMember::where('user_id',$fromUser->id)->whereNotIn('group_id',$groupIds)->update(['user_id'=>$user->id]);
        //合并关注
        $attentions = Attention::where('user_id',$fromUser->id)->get();
        foreach ($attentions as $attention) {
            $existA = Attention::where('user_id',$user->id)
                ->where('source_id',$attention->source_id)
                ->where('source_type',$attention->source_type)->first();
            if (!$existA) {
                $attention->user_id = $user->id;
                $attention->save();
            }
        }
        $attentionUsers = Attention::where('source_id',$fromUser->id)
            ->where('source_type',get_class($user))->get();
        foreach ($attentionUsers as $attentionUser) {
            $existB = Attention::where('user_id',$attentionUser->user_id)
                ->where('source_id',$user->id)
                ->where('source_type',get_class($user))->first();
            if (!$existB) {
                $attentionUser->source_id = $user->id;
                $attentionUser->save();
            }
        }
        Doing::where('user_id',$fromUser->id)->update(['user_id'=>$user->id]);
        //3.用户状态改为不可用
        $fromUser->status = -1;
        $fromUser->save();
    }

    public static function genRcCode(){
        $code = strtolower(Str::random(6));
        while(self::where('rc_code',$code)->first()){
            $code = strtolower(Str::random(6));
        }
        return $code;
    }

}
