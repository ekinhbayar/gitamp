<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Event\GitHub;

use ekinhbayar\GitAmp\Event\BaseEvent;
use ekinhbayar\GitAmp\Presentation\Information;
use ekinhbayar\GitAmp\Presentation\Type;
use ekinhbayar\GitAmp\Presentation\Ring;
use ekinhbayar\GitAmp\Presentation\Sound\BaseSound;
use ekinhbayar\GitAmp\Presentation\Sound\Swell;
use ekinhbayar\GitAmp\Presentation\Sound\SwellEgg;

class PullRequestEvent extends BaseEvent
{
    /**
     * @param array<string,mixed> $event
     * @param array<string> $specialRepositories
     */
    public function __construct(array $event, array $specialRepositories)
    {
        parent::__construct(
            (int) $event['id'],
            new Type(Type::PR_ACTION),
            new Information(
                $event['payload']['pull_request']['html_url'],
                $event['payload']['pull_request']['title'],
                $this->buildMessage($event),
            ),
            new Ring(10000, 600),
            $this->buildSound($event, $specialRepositories),
        );
    }

    /**
     * @param array<string,mixed> $event
     */
    private function buildMessage(array $event): string
    {
        return \sprintf(
            '%s %s a PR for %s',
            $event['actor']['login'],
            $event['payload']['action'],
            $event['repo']['name'],
        );
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
