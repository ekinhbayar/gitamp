<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Event\GitHub;

use ekinhbayar\GitAmp\Event\BaseEvent;
use ekinhbayar\GitAmp\Presentation\Information;
use ekinhbayar\GitAmp\Presentation\Type;
use ekinhbayar\GitAmp\Presentation\Ring;
use ekinhbayar\GitAmp\Presentation\Sound\BaseSound;
use ekinhbayar\GitAmp\Presentation\Sound\Swell;
use ekinhbayar\GitAmp\Presentation\Sound\SwellEgg;

class WatchEvent extends BaseEvent
{
    public function __construct(array $event)
    {
        parent::__construct(
            (int) $event['id'],
            new Type(7),
            new Information($this->buildUrl($event), $this->buildPayload(), $this->buildMessage($event)),
            new Ring(3000, 80),
            $this->buildSound($event)
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
        return \sprintf('%s watched %s', $event['actor']['login'], $event['repo']['name']);
    }

    private function buildSound(array $event): BaseSound
    {
        if ($event['repo']['name'] === 'ekinhbayar/gitamp') {
            return new SwellEgg();
        }

        return new Swell();
    }
}
