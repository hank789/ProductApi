<?php namespace App\Api\Controllers\Account;

use App\Api\Controllers\Controller;
use App\Events\Frontend\Auth\UserLoggedIn;
use App\Events\Frontend\Auth\UserLoggedOut;
use App\Events\Frontend\Auth\UserRegistered;
use App\Events\Frontend\System\ExceptionNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Jobs\SendPhoneMessage;
use App\Models\Attention;
use App\Models\Credit;
use App\Models\Doing;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\IM\Message;
use App\Models\IM\Room;
use App\Models\IM\RoomUser;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserOauth;
use App\Models\UserRegistrationCode;
use App\Services\RateLimiter;
use App\Services\Registrar;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\JWTAuth;


class AuthController extends Controller
{
    //发送手机验证码
    public function sendPhoneCode(Request $request)
    {
        $validateRules = [
            'mobile' => 'required|cn_phone',
            'type'   => 'required|in:register,login,change,wx_gzh_register,weapp_register,change_phone'
        ];

        $this->validate($request,$validateRules);
        $mobile = $request->input('mobile');
        $type   = $request->input('type');
        if(RateLimiter::instance()->increase('sendPhoneCode:'.$type,$mobile,120,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        $user = User::where('mobile',$mobile)->first();
        switch($type){
            case 'register':
                if($user){
                    throw new ApiException(ApiException::USER_PHONE_EXIST);
                }
                break;
            case 'weapp_register':
                //微信小程序验证码
                break;
            case 'change_phone':
                //换绑手机号
                break;
            case 'login':
                //登陆
                break;
            default:
                if(!$user){
                    throw new ApiException(ApiException::USER_NOT_FOUND);
                }
                break;
        }

        $code = makeVerifyCode();
        dispatch((new SendPhoneMessage($mobile,['code' => $code],$type)));
        Cache::put(SendPhoneMessage::getCacheKey($type,$mobile), $code, 6);
        return self::createJsonData(true);
    }

    //刷新token
    public function refreshToken(Request $request,JWTAuth $JWTAuth){
        try {
            $newToken = $JWTAuth->setRequest($request)->parseToken()->refresh();
        } catch (TokenExpiredException $e) {
            return self::createJsonData(false,[],ApiException::TOKEN_EXPIRED,'token已失效')->setStatusCode($e->getStatusCode());
        } catch (JWTException $e) {
            return self::createJsonData(false,[],ApiException::TOKEN_INVALID,'token无效')->setStatusCode($e->getStatusCode());
        }
        // send the refreshed token back to the client
        return static::createJsonData(true,['token'=>$newToken],ApiException::SUCCESS,'ok')->header('Authorization', 'Bearer '.$newToken);
    }

    public function login(Request $request,JWTAuth $JWTAuth){

        $validateRules = [
            'mobile' => 'required',
            'password' => 'required_without:phoneCode',
            'phoneCode' => 'required_without:password'
        ];

        $this->validate($request,$validateRules);

        /*只接收mobile和password的值*/
        $credentials = $request->only('mobile', 'password', 'phoneCode');
        $isNewUser = 0;
        if(RateLimiter::instance()->increase('userLogin',$credentials['mobile'],3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        if(RateLimiter::instance()->increase('userLoginCount',$credentials['mobile'],60,30)){
            event(new ExceptionNotify('用户登录['.$credentials['mobile'].']60秒内尝试了30次以上'));
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        if (isset($credentials['phoneCode']) && $credentials['phoneCode']) {
            //验证手机验证码
            $code_cache = Cache::get(SendPhoneMessage::getCacheKey('login',$credentials['mobile']));
            if($code_cache != $credentials['phoneCode']){
                throw new ApiException(ApiException::ARGS_YZM_ERROR);
            }
            $user = User::where('mobile',$credentials['mobile'])->first();
            if (!$user) {
                throw new ApiException(ApiException::USER_NOT_FOUND);
            }
            $token = $JWTAuth->fromUser($user);
            $loginFrom = '短信验证码';
        } else {
            $token = $JWTAuth->attempt($credentials);
            $user = $request->user();
            $loginFrom = '网站';
        }

        /*根据邮箱地址和密码进行认证*/
        if ($token)
        {
            $device_code = $request->input('deviceCode');
            if ($device_code) {
                $loginFrom = 'App';
            }
            if($user->last_login_token && $device_code){
                try {
                    $JWTAuth->refresh($user->last_login_token);
                } catch (\Exception $e){
                    \Log::error($e->getMessage());
                }
            }
            $user->last_login_token = $token;
            $user->save();
            if($user->status != 1) {
                throw new ApiException(ApiException::USER_SUSPEND);
            }
            //登陆事件通知
            event(new UserLoggedIn($user, $loginFrom));

            $info = [];
            $info['token'] = $token;
            $info['newUser'] = $isNewUser;
            $info['id'] = $user->id;
            $info['name'] = $user->name;
            $info['mobile'] = $user->mobile;
            $info['email'] = $user->email;
            $info['avatar_url'] = $user->getAvatarUrl();
            $info['gender'] = $user->gender;
            $info['birthday'] = $user->birthday;
            $info['province'] = $user->province;
            $info['city'] = $user->city;
            $info['company'] = $user->company;
            $info['title'] = $user->title;
            $info['description'] = $user->description;
            $info['status'] = $user->status;
            $info['address_detail'] = $user->address_detail;
            $info['industry_tags'] = array_column($user->industryTags(),'name');
            $info['tags'] = Tag::whereIn('id',$user->userTag()->pluck('tag_id'))->pluck('name');

            /*认证成功*/
            return static::createJsonData(true,$info,ApiException::SUCCESS);

        }

        return static::createJsonData(false,[],ApiException::USER_PASSWORD_ERROR,'用户名或密码错误');

    }

        /*忘记密码*/
    public function forgetPassword(Request $request)
    {

        /*表单数据校验*/
        $this->validate($request, [
            'mobile' => 'required|cn_phone',
            'code' => 'required',
            'password' => 'required|min:6|max:64',
        ]);
        $mobile = $request->input('mobile');
        if(RateLimiter::instance()->increase('userForgetPassword',$mobile,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        if(RateLimiter::instance()->increase('userForgetPasswordCount',$mobile,60,30)){
            event(new ExceptionNotify('忘记密码['.$mobile.']60秒内尝试了30次以上'));
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        //验证手机验证码
        $code_cache = Cache::get(SendPhoneMessage::getCacheKey('change',$mobile));
        $code = $request->input('code');
        if($code_cache != $code){
            throw new ApiException(ApiException::ARGS_YZM_ERROR);
        }

        $user = User::where('mobile',$mobile)->first();
        if(!$user){
            throw new ApiException(ApiException::USER_NOT_FOUND);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return self::createJsonData(true);

    }



    /**
     * 用户登出
     */
    public function logout(Request $request,Guard $auth){
        //通知
        event(new UserLoggedOut($auth->user()));
        $data = $request->all();
        return self::createJsonData(true);
    }

}
