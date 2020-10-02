<?php declare(strict_types=1);

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
    /**
     * @param array<string,mixed> $event
     * @param array<string> $specialRepositories
     */
    public function __construct(array $event, array $specialRepositories)
    {
        parent::__construct(
            (int) $event['id'],
            new Type(Type::STARTED_WATCHING),
            new Information($this->buildUrl($event), $this->buildPayload(), $this->buildMessage($event)),
            new Ring(3000, 80),
            $this->buildSound($event, $specialRepositories),
        );
    }

    /**
     * @param array<string,mixed> $event
     */
    private function buildUrl(array $event): string
    {
        return 'https://github.com/' . $event['repo']['name'];
    }

    /**
     * @param array<string,mixed> $event
     */
    private function buildPayload(): string
    {
        return 'not sure if stupid but works anyway';
    }

    /**
     * @param array<string,mixed> $event
     */
    private function buildMessage(array $event): string
    {
        return \sprintf('%s watched %s', $event['actor']['login'], $event['repo']['name']);
    }

    /**
     * @param array<string,mixed> $event
     * @param array<string> $specialRepositories
     */
    private function buildSound(array $event, array $specialRepositories): BaseSound
    {
        if (in_array($event['repo']['name'], $specialRepositories, true)) {
            return new SwellEgg();
        }

        return new Swell();
    }
}
