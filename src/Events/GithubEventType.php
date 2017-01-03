<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Events;

/**
 * http://api.github.com/events
 */
class GithubEventType
{
    const TYPES = [
        "ForkEvent", "CreateEvent", "WatchEvent", "PushEvent",
        /*"GollumEvent",*/ "IssueCommentEvent", "IssuesEvent", "PullRequestEvent"
        /*"DeploymentEvent", "DeploymentStatusEvent", "DownloadEvent",
        "CommitCommentEvent", "FollowEvent",  "ForkApplyEvent", "GistEvent",
        "MemberEvent", "MembershipEvent", "MilestoneEvent", "OrganizationEvent",
        "PageBuildEvent", "PublicEvent", "LabelEvent", "PullRequestReviewEvent",
        "PullRequestReviewCommentEvent", "ReleaseEvent", "RepositoryEvent",
        "StatusEvent", "TeamEvent", "TeamAddEvent", "DeleteEvent",*/
    ];

    public function isValid(string $type): bool
    {
        return in_array($type, self::TYPES, true);
    }
}
