<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Events\Type;

use ekinhbayar\GitAmp\Presentation\NumericalType;
use ekinhbayar\GitAmp\Presentation\Ring;
use ekinhbayar\GitAmp\Presentation\Sound\BaseSound;
use ekinhbayar\GitAmp\Presentation\Sound\Clav;
use ekinhbayar\GitAmp\Presentation\Sound\ClavEgg;

class IssuesEvent extends BaseEvent
{
    public function __construct(array $event)
    {
        parent::__construct(
            (int) $event['id'],
            new NumericalType(3),
            'IssuesEvent',
            $event['repo']['name'],
            $event['payload']['issue']['html_url'],
            $event['payload']['issue']['title'],
            $this->buildMessage($event),
            new Ring(3000, 80),
            $this->buildSound($event)
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

    private function buildSound(array $event): BaseSound
    {
        if ($event['repo']['name'] === 'ekinhbayar/gitamp') {
            return new ClavEgg();
        }

        return new Clav(strlen($event['payload']['issue']['title']) * 1.1);
    }
}
