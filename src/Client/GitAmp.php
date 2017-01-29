<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Client;

use Amp\Artax\Response;
use Amp\Promise;
use Amp\Artax\Client;
use Amp\Artax\ClientException;
use Amp\Artax\Request;
use Amp\Success;
use ekinhbayar\GitAmp\Response\Factory;
use ekinhbayar\GitAmp\Github\Credentials;
use Psr\Log\LoggerInterface;

class GitAmp
{
    const API_ENDPOINT = 'https://api.github.com/events';

    private $client;

    private $credentials;

    private $resultFactory;

    private $logger;

    public function __construct(
        Client $client,
        Credentials $credentials,
        Factory $resultFactory,
        LoggerInterface $logger
    )
    {
        $this->client        = $client;
        $this->credentials   = $credentials;
        $this->resultFactory = $resultFactory;
        $this->logger        = $logger;
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
                $this->checkForRequestErrors($error, $result);
            });

            return $promise;

        } catch (ClientException $e) {
            throw new RequestFailedException('Failed to send GET request to API endpoint', $e->getCode(), $e);
        }
    }

    private function checkForRequestErrors($error, $result)
    {
        if ($error) {
            $this->logger->error('Call to webservice failed', ['error' => $error]);

            throw new RequestFailedException('Call to webservice failed');
        }

        /** @var Response $result */
        if ($result->getStatus() !== 200) {
            $message = sprintf(
                'A non-200 response status (%s - %s) was encountered',
                $result->getStatus(),
                $result->getReason()
            );

            $this->logger->critical($message, ['result' => $result]);

            throw new RequestFailedException($message);
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

