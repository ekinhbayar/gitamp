<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Events\Type;

use ekinhbayar\GitAmp\Presentation\NumericalType;
use ekinhbayar\GitAmp\Presentation\Ring;

class IssueCommentEvent extends BaseEvent
{
    public function __construct(array $event)
    {
        parent::__construct(
            (int) $event['id'],
            new NumericalType(4),
            'IssueCommentEvent',
            $event['payload']['action'],
            $event['repo']['name'],
            $event['actor']['login'],
            $event['payload']['issue']['html_url'],
            $this->buildMessage($event),
            $this->buildMessage($event),
            new Ring(3000, 80)
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
