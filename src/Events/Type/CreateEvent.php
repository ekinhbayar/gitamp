<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Events\Type;

use ekinhbayar\GitAmp\Presentation\Information;
use ekinhbayar\GitAmp\Presentation\Type;
use ekinhbayar\GitAmp\Presentation\Ring;
use ekinhbayar\GitAmp\Presentation\Sound\BaseSound;
use ekinhbayar\GitAmp\Presentation\Sound\Swell;
use ekinhbayar\GitAmp\Presentation\Sound\SwellEgg;

class CreateEvent extends BaseEvent
{
    public function __construct(array $event)
    {
        parent::__construct(
            (int) $event['id'],
            new Type(6),
            new Information($this->buildUrl($event), $this->buildPayload($event), $this->buildMessage($event)),
            new Ring(3000, 80),
            $this->buildSound($event)
        );
    }

    private function buildUrl(array $event): string
    {
        return 'https://github.com/' . $event['repo']['name'];
    }

    private function buildPayload(array $event): string
    {
        if (isset($event['payload']['description'])) {
            return $event['payload']['description'];
        }

        return 'https://github.com/' . $event['repo']['name'];
    }

    private function buildMessage(array $event): string
    {
        return sprintf('%s created %s', $event['actor']['login'], $event['repo']['name']);
    }

    private function buildSound(array $event): BaseSound
    {
        if ($event['repo']['name'] === 'ekinhbayar/gitamp') {
            return new SwellEgg();
        }

        return new Swell();
    }
}
