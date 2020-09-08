<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Http;

class Origin
{
    private array $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function get(): string
    {
        if (isset($this->configuration['ssl'])) {
            return $this->getHttpsOrigin();
        }

        return $this->getHttpOrigin();
    }

    private function getHttpsOrigin(): string
    {
        $origin = 'https://' . $this->configuration['hostname'];

        if ($this->configuration['ssl']['port'] !== 443) {
            $origin .= ':' . $this->configuration['ssl']['port'];
        }

        return $origin;
    }

    private function getHttpOrigin(): string
    {
        $origin = 'http://' . $this->configuration['hostname'];

        if ($this->configuration['expose']['port'] !== 80) {
            $origin .= ':' . $this->configuration['expose']['port'];
        }

        return $origin;
    }
}
