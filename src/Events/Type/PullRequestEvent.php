<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Events\Type;

use ekinhbayar\GitAmp\Presentation\NumericalType;
use ekinhbayar\GitAmp\Presentation\Ring;

class PullRequestEvent extends BaseEvent
{
    public function __construct(array $event)
    {
        parent::__construct(
            (int) $event['id'],
            new NumericalType(2),
            'PullRequestEvent',
            $event['payload']['action'],
            $event['repo']['name'],
            $event['actor']['login'],
            $event['payload']['pull_request']['html_url'],
            $event['payload']['pull_request']['title'],
            $this->buildMessage($event),
            new Ring(10000, 600)
        );
    }

    private function buildMessage(array $event): string
    {
        return sprintf(
            '%s %s a PR for %s',
            $event['actor']['login'],
            $event['payload']['action'],
            $event['repo']['name']
        );
    }
}
