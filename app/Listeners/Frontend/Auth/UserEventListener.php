<?php

namespace App\Listeners\Frontend\Auth;
use App\Events\Frontend\Auth\UserRegistered;
use App\Models\Attention;
use App\Models\Credit;
use App\Models\Feed\Feed;
use App\Models\IM\MessageRoom;
use App\Models\IM\Room;
use App\Models\IM\RoomUser;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Models\UserOauth;
use App\Models\UserTag;
use App\Notifications\NewInviteUserRegister;
use App\Notifications\NewMessage;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Redis;
use App\Events\Frontend\System\Credit as CreditEvent;

/**
 * Class UserEventListener.
 */
class UserEventListener implements ShouldQueue
{

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    /**
     * @param $event
     */
    public function onLoggedIn($event)
    {
        \Slack::send('用户登录: '.formatSlackUser($event->user).';设备:'.$event->loginFrom);
    }

    /**
     * @param $event
     */
    public function onLoggedOut($event)
    {
        \Slack::send('用户登出: '.formatSlackUser($event->user).';设备:'.$event->from);
    }

    /**
     * @param UserRegistered $event
     */
    public function onRegistered($event)
    {

        if ($event->oauthDataId) {
            $oauthData = UserOauth::find($event->oauthDataId);
            $event->user->avatar = saveImgToCdn($oauthData->avatar);
            $event->user->save();
        }
        $title = '';
        //加默认tag
        UserTag::create([
            'user_id' => $event->user->id,
            'tag_id'  => 0,
        ]);

        \Slack::send('新用户注册: '.formatSlackUser($event->user).'；设备：'.$event->from.$title);
    }

    /**
     * @param $event
     */
    public function onConfirmed($event)
    {
        \Slack::send('User Confirmed: '.$event->user->name);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            \App\Events\Frontend\Auth\UserLoggedIn::class,
            'App\Listeners\Frontend\Auth\UserEventListener@onLoggedIn'
        );

        $events->listen(
            \App\Events\Frontend\Auth\UserLoggedOut::class,
            'App\Listeners\Frontend\Auth\UserEventListener@onLoggedOut'
        );

        $events->listen(
            \App\Events\Frontend\Auth\UserRegistered::class,
            'App\Listeners\Frontend\Auth\UserEventListener@onRegistered'
        );

        $events->listen(
            \App\Events\Frontend\Auth\UserConfirmed::class,
            'App\Listeners\Frontend\Auth\UserEventListener@onConfirmed'
        );
    }
}
