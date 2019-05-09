<?php namespace App\Api\Controllers\Account;

use App\Cache\UserCache;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Jobs\SendPhoneMessage;
use App\Logic\TagsLogic;
use App\Logic\WithdrawLogic;
use App\Models\AddressBook;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Collection;
use App\Models\Comment;
use App\Models\Credit;
use App\Models\Doing;
use App\Models\Feed\Feed;
use App\Models\Feedback;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Pay\MoneyLog;
use App\Models\Pay\UserMoney;
use App\Models\ProductUserRel;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Models\UserOauth;
use App\Models\UserTag;
use App\Services\City\CityData;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use App\Api\Controllers\Controller;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class ProfileController extends Controller
{

    /*个人基本资料*/
    public function info(Request $request)
    {
        /**
         * @var User
         */
        $user = $request->user();
        $data = Cache::get('user_info_'.$user->id);
        if (!$data) {
            $info = [];
            $info['id'] = $user->id;
            $info['uuid'] = $user->uuid;
            $info['name'] = $user->name;
            $info['realname'] = $user->realname;
            $info['mobile'] = $user->mobile;
            $info['email'] = $user->email;
            $info['rc_code'] = $user->rc_code;
            $info['avatar_url'] = $user->avatar;
            $info['gender'] = $user->gender;
            $info['birthday'] = $user->birthday;


            $info['title'] = $user->title;
            $info['description'] = $user->description;
            $info['status'] = $user->status;
            $info['address_detail'] = $user->address_detail;



            $data = [
                'info'   => $info
            ];
            Cache::forever('user_info_'.$user->id,$data);
        }

        $data['productManager'] = false;
        $managerPros = ProductUserRel::where('user_id',$user->id)->where('status',1)->count();
        if ($managerPros > 0) {
            $data['productManager'] = true;
        }

        return self::createJsonData(true,$data,ApiException::SUCCESS,'ok');
    }

    /**
     * 修改用户密码
     * @param Request $request
     */
    public function updatePassword(Request $request)
    {
        $validateRules = [
            'old_password' => 'required|min:6|max:16',
            'password' => 'required|min:6|max:16',
            'password_confirmation'=>'same:password',
        ];
        $this->validate($request,$validateRules);

        $user = $request->user();

        if(Hash::check($request->input('old_password'),$user->password)){
            $user->password = Hash::make($request->input('password'));
            $user->save();
            Auth()->logout();
            return self::createJsonData(true,[],ApiException::SUCCESS,'密码修改成功,请重新登录');
        }

        return self::createJsonData(false,[],ApiException::USER_PASSWORD_ERROR,'原始密码错误');
    }

}
