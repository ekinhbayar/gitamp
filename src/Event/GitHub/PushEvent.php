<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Event\GitHub;

use ekinhbayar\GitAmp\Event\BaseEvent;
use ekinhbayar\GitAmp\Presentation\Information;
use ekinhbayar\GitAmp\Presentation\Ring;
use ekinhbayar\GitAmp\Presentation\Sound\BaseSound;
use ekinhbayar\GitAmp\Presentation\Sound\Celesta;
use ekinhbayar\GitAmp\Presentation\Sound\CelestaEgg;
use ekinhbayar\GitAmp\Presentation\Type;

class PushEvent extends BaseEvent
{
    /**
     * @param array<string,mixed> $event
     * @param array<string> $specialRepositories
     */
    public function __construct(array $event, array $specialRepositories)
    {
        parent::__construct(
            (int) $event['id'],
            new Type(Type::PUSH_TO_REPOSITORY),
            new Information($this->buildUrl($event), $this->buildPayload($event), $this->buildMessage($event)),
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
    private function buildPayload(array $event): string
    {
        if (isset($event['payload']['commits'][0]['message'])) {
            return $event['payload']['commits'][0]['message'];
        }

        return 'https://github.com/' . $event['actor']['login'];
    }

    /**
     * @param array<string,mixed> $event
     */
    private function buildMessage(array $event): string
    {
        return \sprintf('%s pushed to %s', $event['actor']['login'], $event['repo']['name']);
    }

    /**
     * @param array<string,mixed> $event
     * @param array<string> $specialRepositories
     */
    private function buildSound(array $event, array $specialRepositories): BaseSound
    {
        if (in_array($event['repo']['name'], $specialRepositories, true)) {
            return new CelestaEgg();
        }

        return new Celesta(\strlen($this->buildPayload($event)) * 1.1);
    }
}
