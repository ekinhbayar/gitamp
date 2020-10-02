<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Event\GitHub;

use ekinhbayar\GitAmp\Event\BaseEvent;
use ekinhbayar\GitAmp\Presentation\Information;
use ekinhbayar\GitAmp\Presentation\Ring;
use ekinhbayar\GitAmp\Presentation\Sound\BaseSound;
use ekinhbayar\GitAmp\Presentation\Sound\Clav;
use ekinhbayar\GitAmp\Presentation\Sound\ClavEgg;
use ekinhbayar\GitAmp\Presentation\Type;

class IssuesEvent extends BaseEvent
{
    /**
     * @param array<string,mixed> $event
     * @param array<string> $specialRepositories
     */
    public function __construct(array $event, array $specialRepositories)
    {
        parent::__construct(
            (int) $event['id'],
            new Type(Type::ISSUE_ACTION),
            new Information(
                $event['payload']['issue']['html_url'],
                $event['payload']['issue']['title'],
                $this->buildMessage($event),
            ),
            new Ring(3000, 80),
            $this->buildSound($event, $specialRepositories),
        );
    }

    /**
     * @param array<string,mixed> $event
     */
    private function buildMessage(array $event): string
    {
        return \sprintf(
            '%s %s an issue in %s',
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
            return new ClavEgg();
        }

        return new Clav(\strlen($event['payload']['issue']['title']) * 1.1);
    }
}
