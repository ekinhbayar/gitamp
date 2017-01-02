<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Response;

use Amp\Artax\Response;
use ekinhbayar\GitAmp\Events\GithubEvent;
use ExceptionalJSON\DecodeErrorException;

class Results
{
    private $results;

    public function __construct( $response)
    {
        $this->results = $response;
    }

    public function getStatus(Response $response)
    {
        return $response->getStatus();
    }

    public function parseResults(Response $response): array
    {
        $payload = [];
        try {
            $payload = json_try_decode($response->getBody(), true);
        } catch (DecodeErrorException $e) {
            throw new DecodingFailed('Failed to decode response body as JSON', $e->getCode(), $e);
        }
        return $payload;
    }

    public function createEventsFromResultSet(array $set): array
    {
        $events = [];
        foreach (new ResultsIterator($set) as $event) {

            if(!in_array($event['type'], GithubEvent::TYPES)) continue;

            $id = $event['id'];
            $type = $event['type'];
            $actorName = $event['actor']['login'];
            $repoName  = $event['repo']['name'];
            $actorURL  = "https://github.com/" . $actorName;
            $repoURL   = "https://github.com/" . $repoName;

            // action & event URL & message are conditional to the type of event
            // todo: implement more event types
            switch ($type) {
                case 'WatchEvent':
                    $action = $event['payload']['action'];
                    $eventURL = $repoURL;
                    $message = 'not sure if stupid but works anyway';
                    break;
                case 'CreateEvent':
                    $action = 'created';
                    $eventURL = $repoURL;
                    $message = $event['payload']['description'] ?? $repoURL;
                    break;
                case 'ForkEvent':
                    $action = 'forked';
                    $eventURL = $repoURL;
                    $message = 'not sure if stupid but works anyway';
                    break;
                /*case 'GollumEvent':   todo: for some reason url doesn't work, check that
                    $action = $event['payload']['pages']['action'];
                    $eventURL = $event['payload']['pages']['html_url'];
                    break;*/
                case 'IssuesEvent':
                    $action = $event['payload']['action'];
                    $eventURL = $event['payload']['issue']['html_url'];
                    $message = $event['payload']['issue']['title'];
                    break;
                case 'IssueCommentEvent':
                    $action = $event['payload']['action'];
                    $eventURL = $event['payload']['issue']['html_url'];
                    $message = $event['comment']['body'] ?? $event['payload']['issue']['title'];;
                    break;
                case 'PullRequestEvent':
                    $action = $event['payload']['action'];
                    $eventURL = $event['payload']['pull_request']['html_url'];
                    $message = $event['payload']['pull_request']['title'];
                    break;
                case 'PushEvent':
                    $action = 'pushed to';
                    $eventURL = $repoURL;
                    $message = $event['payload']['commits'][0]['message'] ?? $eventURL;
                    break;
                default:
                    $action = 'was playing with';
                    $eventURL = $repoURL;
                    $message = 'not sure if stupid but works anyway';

            }

            $events[] = new GithubEvent($id, $type, urlencode($eventURL), $action, $message, $actorName, urlencode($actorURL), $repoName, urlencode($repoURL));
        }

        return $events;
    }

}