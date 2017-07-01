<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Events\Type;

use ekinhbayar\GitAmp\Presentation\NumericalType;
use ekinhbayar\GitAmp\Presentation\Ring;

class IssuesEvent extends BaseEvent
{
    public function __construct(array $event)
    {
        parent::__construct(
            (int) $event['id'],
            new NumericalType(3),
            'IssuesEvent',
            $event['payload']['action'],
            $event['repo']['name'],
            $event['actor']['login'],
            $event['payload']['issue']['html_url'],
            $event['payload']['issue']['title'],
            $this->buildMessage($event),
            new Ring(3000, 80)
        );
    }

    private function buildMessage(array $event): string
    {
        return sprintf(
            '%s %s an issue in %s',
            $event['actor']['login'],
            $event['payload']['action'],
            $event['repo']['name']
        );
    }
}
