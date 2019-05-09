<?php

namespace App\Jobs;

use App\Logic\TagsLogic;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\User;
use App\Services\RateLimiter;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class NewSubmissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $id;

    public $notifyAutoChannel = false;

    public $additionalSlackMsg = '';


    public function __construct($id, $notifyAutoChannel = false, $additionalSlackMsg='')
    {
        $this->id = $id;
        $this->notifyAutoChannel = $notifyAutoChannel;
        $this->additionalSlackMsg = $additionalSlackMsg;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $submission = Submission::find($this->id);
        if (!$submission) return;
        if ($submission->status == 0) return;
        $slackFields = [];
        foreach ($submission->data as $field=>$value){
            if ($value){
                if (!is_array($value) && in_array($field,['url','title'])) {
                    $slackFields[] = [
                        'title' => $field,
                        'value' => $value
                    ];
                }
            }
        }

        $user = User::find($submission->user_id);

        RateLimiter::instance()->lock_acquire('upload-image-submission-'.$submission->id);
        $submission->increment('views');

        $typeName = '分享';
        $targetName = '';
        switch ($submission->type) {
            case 'link':
            case 'text':
                $typeName = '分享';
                break;
            case 'article':
                $typeName = '文章';
                break;
            case 'review':
                $typeName = '点评';
                if (isset($submission->data['category_ids'])) {
                    foreach ($submission->data['category_ids'] as $category_id) {
                        $tagC = TagCategoryRel::where('tag_id',$submission->category_id)->where('category_id',$category_id)->first();
                        $tagC->calcRate();
                    }
                }
                $tag = Tag::find($submission->category_id);
                $tag->reviews += 1;
                $tag->save();
                $targetName = '在产品['.$tag->name.']';
                TagsLogic::delProductCache();
                dispatch(new UpdateProductInfoCache($tag->id));
                if (isset($submission->data['real_author']) && $submission->data['real_author']) {
                    $real_author = User::find($submission->data['real_author']);
                    $this->additionalSlackMsg .= '运营人员：'.formatSlackUser($real_author).';';
                }
                $url = config('app.mobile_url').'#/dianping/comment/'.$submission->slug;
                break;
        }
        if ($submission->type != 'review') {
            $url = config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug;
            $submission->setKeywordTags();
        } else {
            $data = $submission->data;
            $data['keywords'] = implode(',',$submission->tags->pluck('name')->toArray());
            $submission->data = $data;
            $submission->save();
        }
        $submission->calculationRate();
        RateLimiter::instance()->lock_release('upload-image-submission-'.$submission->id);

        $submission->getRelatedProducts();
        $channel = config('slack.ask_activity_channel');
        if ($this->notifyAutoChannel) {
            $channel = config('slack.auto_channel');
        }

        return \Slack::to($channel)
            ->disableMarkdown()
            ->attach(
                [
                    'text' => strip_tags($submission->title),
                    'pretext' => '[链接]('.$url.')',
                    'author_name' => $user->name,
                    'author_link' => $url,
                    'mrkdwn_in' => ['pretext'],
                    'color'     => 'good',
                    'fields' => $slackFields
                ]
            )->send($this->additionalSlackMsg.'用户'.formatSlackUser($user).$targetName.'提交了新'.$typeName);
    }
}
