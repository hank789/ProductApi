<?php

namespace App\Listeners\Frontend;
use App\Events\Frontend\System\ExceptionNotify;
use App\Events\Frontend\System\ImportantNotify;
use App\Events\Frontend\System\OperationNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Events\Frontend\System\Feedback;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;


/**
 * Class UserEventListener.
 */
class SystemEventListener implements ShouldQueue
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
    public function feedback($event)
    {
        \Slack::to(config('slack.ask_activity_channel'))->send('用户['.$event->user->name.']['.$event->user->mobile.']['.$event->title.']:'.$event->content);
    }

    /**
     * @param systemNotify $event
     */
    public function systemNotify($event){
        try {
            \Slack::to(config('slack.ask_activity_channel'))
                ->attach(
                    [
                        'fields' => $event->fields
                    ]
                )
                ->send($event->message);
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
        }
    }

    /**
     * @param ExceptionNotify $event
     */
    public function exceptionNotify($event) {
        try {
            \Slack::to(config('slack.exception_channel'))
                ->attach(
                    [
                        'fields' => $event->fields
                    ]
                )
                ->send($event->message);
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
        }
    }

    /**
     * @param OperationNotify $event
     */
    public function operationNotify($event) {
        try {
            \Slack::to(config('slack.operation_channel'))
                ->attach(
                    [
                        'fields' => $event->fields
                    ]
                )
                ->send($event->message);
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
        }
    }

    /**
     * @param ImportantNotify $event
     */
    public function importantNotify($event) {
        try {
            \Slack::to(config('slack.important_channel'))
                ->attach(
                    [
                        'fields' => $event->fields
                    ]
                )
                ->send($event->message);
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
        }
    }



    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            Feedback::class,
            'App\Listeners\Frontend\SystemEventListener@feedback'
        );

        $events->listen(
            SystemNotify::class,
            'App\Listeners\Frontend\SystemEventListener@systemNotify'
        );

        $events->listen(
            ExceptionNotify::class,
            'App\Listeners\Frontend\SystemEventListener@exceptionNotify'
        );

        $events->listen(
            OperationNotify::class,
            'App\Listeners\Frontend\SystemEventListener@operationNotify'
        );
        $events->listen(
            ImportantNotify::class,
            'App\Listeners\Frontend\SystemEventListener@importantNotify'
        );
    }
}
