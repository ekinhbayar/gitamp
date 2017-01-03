<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Events;

/**
 * http://api.github.com/events
 */
class GithubEvent implements Event
{
    public $id;
    public $type;
    public $actorName;
    public $actorURL;
    public $repoName;
    public $repoURL;
    public $eventURL;
    public $action;
    public $message;

    public function __construct(
        string $id, string $type, string $eventURL, string $action, string $message,
        string $actorName, string $actorURL, string $repoName, string $repoURL
    ){
        $this->id = $id;
        $this->type = $type;
        $this->eventURL = $eventURL;
        $this->action = $action;
        $this->message = $message;
        $this->actorName = $actorName;
        $this->actorURL = $actorURL;
        $this->repoName = $repoName;
        $this->repoURL = $repoURL;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEventURL(): string
    {
        return $this->eventURL;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getActorName(): string
    {
        return $this->actorName;
    }

    public function getActorURL(): string
    {
        return $this->actorURL;
    }

    public function getRepoName(): string
    {
        return $this->repoName;
    }

    public function getRepoURL(): string
    {
        return $this->repoURL;
    }
}
