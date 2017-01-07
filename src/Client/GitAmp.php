<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Client;

use Amp\Promise;
use Amp\Artax\Client;
use Amp\Artax\ClientException;
use Amp\Artax\Request;
use Amp\Success;
use ekinhbayar\GitAmp\Events\Factory;
use ekinhbayar\GitAmp\Github\Credentials;
use ekinhbayar\GitAmp\Response\Results;

class GitAmp
{
    const API_ENDPOINT = 'https://api.github.com/events';

    private $client;
    private $credentials;
    private $eventFactory;

    public function __construct(Client $client, Credentials $credentials, Factory $eventFactory)
    {
        $this->client       = $client;
        $this->credentials  = $credentials;
        $this->eventFactory = $eventFactory;
    }

    /**
     * @return Promise
     * @throws RequestFailedException
     */
    private function request(): Promise
    {
        try {
            $request = (new Request)
                ->setMethod('GET')
                ->setUri(self::API_ENDPOINT)
                ->setAllHeaders($this->getAuthHeader());

            $promise = $this->client->request($request);

            $promise->when(function($error, $result) {
                if($result->getStatus() !== 200) {
                    throw new RequestFailedException(
                        'A non-200 response status ('
                        . $result->getStatus() . ' - '
                        . $result->getReason() .') was encountered'
                    );
                }
            });

            return $promise;

        } catch (ClientException $e) {
            throw new RequestFailedException('Failed to send GET request to API endpoint', $e->getCode(), $e);
        }
    }

    public function listen(): Promise
    {
        return \Amp\resolve(function() {
            $response = yield $this->request();

            $results = new Results($this->eventFactory);

            $results->appendResponse($response);

            return new Success($results);
        });
    }

    private function getAuthHeader(): array
    {
        return ['Authorization' => sprintf('Basic %s', $this->credentials->getAuthenticationString())];
    }
}

