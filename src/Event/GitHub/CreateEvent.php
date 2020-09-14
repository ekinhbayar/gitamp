<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Event\GitHub;

use ekinhbayar\GitAmp\Event\BaseEvent;
use ekinhbayar\GitAmp\Presentation\Information;
use ekinhbayar\GitAmp\Presentation\Ring;
use ekinhbayar\GitAmp\Presentation\Sound\BaseSound;
use ekinhbayar\GitAmp\Presentation\Sound\Swell;
use ekinhbayar\GitAmp\Presentation\Sound\SwellEgg;
use ekinhbayar\GitAmp\Presentation\Type;

class CreateEvent extends BaseEvent
{
    /**
     * @param array<string,mixed> $event
     * @param array<string> $specialRepositories
     */
    public function __construct(array $event, array $specialRepositories)
    {
        parent::__construct(
            (int) $event['id'],
            new Type(Type::REPOSITORY_CREATED),
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
        if (isset($event['payload']['description'])) {
            return $event['payload']['description'];
        }

        return 'https://github.com/' . $event['repo']['name'];
    }

    /**
     * @param array<string,mixed> $event
     */
    private function buildMessage(array $event): string
    {
        return \sprintf('%s created %s', $event['actor']['login'], $event['repo']['name']);
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
