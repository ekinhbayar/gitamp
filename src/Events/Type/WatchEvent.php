<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Events\Type;

use ekinhbayar\GitAmp\Presentation\NumericalType;
use ekinhbayar\GitAmp\Presentation\Ring;

class WatchEvent extends BaseEvent
{
    public function __construct(array $event)
    {
        parent::__construct(
            (int) $event['id'],
            new NumericalType(7),
            'WatchEvent',
            $event['repo']['name'],
            $this->buildUrl($event),
            $this->buildPayload(),
            $this->buildMessage($event),
            new Ring(3000, 80)
        );
    }

    private function buildUrl(array $event): string
    {
        return 'https://github.com/' . $event['repo']['name'];
    }

    private function buildPayload(): string
    {
        return 'not sure if stupid but works anyway';
    }

    private function buildMessage(array $event): string
    {
        return sprintf('%s watched %s', $event['actor']['login'], $event['repo']['name']);
    }
}
