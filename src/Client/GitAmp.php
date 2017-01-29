<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Client;

use Amp\Promise;
use Amp\Artax\Client;
use Amp\Artax\ClientException;
use Amp\Artax\Request;
use Amp\Success;
use ekinhbayar\GitAmp\Response\Factory;
use ekinhbayar\GitAmp\Github\Credentials;

class GitAmp
{
    const API_ENDPOINT = 'https://api.github.com/events';

    private $client;

    private $credentials;

    private $resultFactory;

    public function __construct(Client $client, Credentials $credentials, Factory $resultFactory)
    {
        $this->client        = $client;
        $this->credentials   = $credentials;
        $this->resultFactory = $resultFactory;
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
                if ($result->getStatus() !== 200) {
                    throw new RequestFailedException(
                        'A non-200 response status ('
                        . $result->getStatus() . ' - '
                        . $result->getReason() . ') was encountered'
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

            return new Success($this->resultFactory->build($response));
        });
    }

    private function getAuthHeader(): array
    {
        return ['Authorization' => sprintf('Basic %s', $this->credentials->getAuthenticationString())];
    }
}

