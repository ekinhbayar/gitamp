<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Event\GitHub;

use ekinhbayar\GitAmp\Event\BaseEvent;
use ekinhbayar\GitAmp\Presentation\Information;
use ekinhbayar\GitAmp\Presentation\Ring;
use ekinhbayar\GitAmp\Presentation\Sound\BaseSound;
use ekinhbayar\GitAmp\Presentation\Sound\Clav;
use ekinhbayar\GitAmp\Presentation\Sound\ClavEgg;
use ekinhbayar\GitAmp\Presentation\Type;

class IssueCommentEvent extends BaseEvent
{
    private const SPECIAL_REPOSITORIES = [
        'ekinhbayar/gitamp',
        'amphp/amp',
    ];

    public function __construct(array $event)
    {
        parent::__construct(
            (int) $event['id'],
            new Type(4),
            new Information(
                $event['payload']['issue']['html_url'],
                $this->buildPayload($event),
                $this->buildMessage($event)
            ),
            new Ring(3000, 80),
            $this->buildSound($event),
        );
    }

    private function buildPayload(array $event): string
    {
        if (isset($event['comment']['body'])) {
            return $event['comment']['body'];
        }

        return $event['payload']['issue']['title'];
    }

    private function buildMessage(array $event): string
    {
        return \sprintf('%s commented in %s', $event['actor']['login'], $event['repo']['name']);
    }

    private function buildSound(array $event): BaseSound
    {
        if (\in_array($event['repo']['name'], self::SPECIAL_REPOSITORIES, true)) {
            return new ClavEgg();
        }

        return new Clav(\strlen($this->buildPayload($event)) * 1.1);
    }
}
