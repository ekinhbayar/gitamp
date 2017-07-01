<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Events\Type;

use ekinhbayar\GitAmp\Presentation\NumericalType;
use ekinhbayar\GitAmp\Presentation\Ring;
use ekinhbayar\GitAmp\Presentation\Sound\BaseSound;
use ekinhbayar\GitAmp\Presentation\Sound\Celesta;
use ekinhbayar\GitAmp\Presentation\Sound\CelestaEgg;

class PushEvent extends BaseEvent
{
    public function __construct(array $event)
    {
        parent::__construct(
            (int) $event['id'],
            new NumericalType(1),
            'PushEvent',
            $event['repo']['name'],
            $this->buildUrl($event),
            $this->buildPayload($event),
            $this->buildMessage($event),
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
        if (isset($event['payload']['commits'][0]['message'])) {
            return $event['payload']['commits'][0]['message'];
        }

        return 'https://github.com/' . $event['actor']['login'];
    }

    private function buildMessage(array $event): string
    {
        return sprintf('%s pushed to %s', $event['actor']['login'], $event['repo']['name']);
    }

    private function buildSound(array $event): BaseSound
    {
        if ($event['repo']['name'] === 'ekinhbayar/gitamp') {
            return new CelestaEgg();
        }

        return new Celesta(strlen($this->buildPayload($event)) * 1.1);
    }
}
