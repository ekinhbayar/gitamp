<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Event\GitHub;

use ekinhbayar\GitAmp\Event\BaseEvent;
use ekinhbayar\GitAmp\Presentation\Information;
use ekinhbayar\GitAmp\Presentation\Type;
use ekinhbayar\GitAmp\Presentation\Ring;
use ekinhbayar\GitAmp\Presentation\Sound\BaseSound;
use ekinhbayar\GitAmp\Presentation\Sound\Celesta;
use ekinhbayar\GitAmp\Presentation\Sound\CelestaEgg;

class PushEvent extends BaseEvent
{
    private const SPECIAL_REPOSITORIES = [
        'ekinhbayar/gitamp',
        'amphp/amp',
    ];

    public function __construct(array $event)
    {
        parent::__construct(
            (int) $event['id'],
            new Type(1),
            new Information($this->buildUrl($event), $this->buildPayload($event), $this->buildMessage($event)),
            new Ring(3000, 80),
            $this->buildSound($event),
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
        return \sprintf('%s pushed to %s', $event['actor']['login'], $event['repo']['name']);
    }

    private function buildSound(array $event): BaseSound
    {
        if (\in_array($event['repo']['name'], self::SPECIAL_REPOSITORIES, true)) {
            return new CelestaEgg();
        }

        return new Celesta(\strlen($this->buildPayload($event)) * 1.1);
    }
}
