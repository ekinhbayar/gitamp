<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Events\Type;

class IssueCommentEvent extends BaseEvent
{
    public function __construct(array $event)
    {
        parent::__construct(
            (int) $event['id'],
            self::class,
            $event['payload']['action'],
            $event['repo']['name'],
            $event['actor']['login'],
            $event['payload']['issue']['html_url'],
            $this->buildMessage($event)
        );
    }

    private function buildMessage(array $event): string
    {
        if (isset($event['comment']['body'])) {
            return $event['comment']['body'];
        }

        return $event['payload']['issue']['title'];
    }
}
