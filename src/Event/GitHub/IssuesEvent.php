<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Event\GitHub;

use ekinhbayar\GitAmp\Event\BaseEvent;
use ekinhbayar\GitAmp\Presentation\Information;
use ekinhbayar\GitAmp\Presentation\Type;
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
            new Type(3),
            new Information(
                $event['payload']['issue']['html_url'],
                $event['payload']['issue']['title'],
                $this->buildMessage($event)
            ),
            new Ring(3000, 80),
            $this->buildSound($event)
        );
    }

    private function buildMessage(array $event): string
    {
        return \sprintf(
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

        return new Clav(\strlen($event['payload']['issue']['title']) * 1.1);
    }
}
