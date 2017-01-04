<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Events\Type;

class IssuesEvent extends BaseEvent
{
    public function __construct(array $event)
    {
        parent::__construct(
            (int) $event['id'],
            'IssuesEvent',
            $event['payload']['action'],
            $event['repo']['name'],
            $event['actor']['login'],
            $event['payload']['issue']['html_url'],
            $event['payload']['issue']['title']
        );
    }
}
