<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use InvalidArgumentException;

class ParsedEndpointUrl
{
    private string $endpointUrl;

    private array $parsedDsn;

    public function __construct(string $endpointUrl)
    {
        $this->endpointUrl = $endpointUrl;

        $this->parsedDsn = parse_url($this->endpointUrl);

        if (!is_array($this->parsedDsn)) {
            throw new InvalidArgumentException('Unable to parse provided DSN for url: ' . $this->endpointUrl);
        }
    }

    public function getEndpointUrl(): string
    {
        return $this->endpointUrl;
    }

    public function validateHost(): self
    {
        if (!isset($this->parsedDsn['host'])) {
            throw new InvalidArgumentException($this->endpointUrl . ' is missing the host');
        }

        return $this;
    }

    public function getHost(): string
    {
        $this->validateHost();

        return $this->parsedDsn['host'];
    }
}
