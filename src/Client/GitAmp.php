<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Client;

use Amp\Promise;
use Amp\Artax\Client;
use Amp\Artax\ClientException;
use Amp\Artax\Request;
use ekinhbayar\GitAmp\Events\GithubEventType;
use ekinhbayar\GitAmp\Github\Credentials;
use ekinhbayar\GitAmp\Response\Results;

/**
 * Class GitAmp
 * @package ekinhbayar\GitAmp\Client
 */
class GitAmp
{
    const API_ENDPOINT = 'https://api.github.com/events';

    /**
     * @var Client
     */
    private $client;
    private $credentials;
    private $githubEventType;

    /**
     * GitAmp constructor.
     * @param Client $client
     */
    public function __construct(Client $client, Credentials $credentials, GithubEventType $githubEventType)
    {
        $this->client          = $client;
        $this->credentials     = $credentials;
        $this->githubEventType = $githubEventType;
    }

    /**
     * @return Promise
     * @throws RequestFailed
     */
    public function request(): Promise
    {
        try {
            $request = (new Request)
                ->setMethod('GET')
                ->setUri(self::API_ENDPOINT)
                ->setAllHeaders($this->getAuthHeader());

            return $this->client->request($request);

        } catch (ClientException $e) {
            throw new RequestFailed("Failed to send GET request to API endpoint", $e->getCode(), $e);
        }

    }

    /**
     * @return \Generator
     */
    public function listen(): \Generator
    {
        $request = yield $this->request();
        $results = new Results($request, $this->githubEventType);
        $set = $results->parseResults($request);
        return $results->createEventsFromResultSet($set);
    }

    private function getAuthHeader(): array
    {
        return ['Authorization' => sprintf('Basic %s', $this->credentials->getAuthenticationString())];
    }
}

