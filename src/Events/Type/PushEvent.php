<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Events\Type;

use ekinhbayar\GitAmp\Presentation\NumericalType;
use ekinhbayar\GitAmp\Presentation\Ring;

class PushEvent extends BaseEvent
{
    public function __construct(array $event)
    {
        parent::__construct(
            (int) $event['id'],
            new NumericalType(1),
            'PushEvent',
            'pushed to',
            $event['repo']['name'],
            $event['actor']['login'],
            $this->buildUrl($event),
            $this->buildMessage($event),
            $this->buildMessage($event),
            new Ring(3000, 80)
        );
    }

    private function buildUrl(array $event): string
    {
        return 'https://github.com/' . $event['repo']['name'];
    }

    private function buildMessage(array $event): string
    {
        if (isset($event['payload']['commits'][0]['message'])) {
            return $event['payload']['commits'][0]['message'];
        }

        return 'https://github.com/' . $event['actor']['login'];
    }
}
