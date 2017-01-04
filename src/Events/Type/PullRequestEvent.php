<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Events\Type;

class PullRequestEvent extends BaseEvent
{
    public function __construct(array $event)
    {
        parent::__construct(
            (int) $event['id'],
            'PullRequestEvent',
            $event['payload']['action'],
            $event['repo']['name'],
            $event['actor']['login'],
            $event['payload']['pull_request']['html_url'],
            $message = $event['payload']['pull_request']['title']
        );
    }
}
