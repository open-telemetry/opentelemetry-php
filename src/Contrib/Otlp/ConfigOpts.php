<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use InvalidArgumentException;


class ConfigOpts {

    private $endpoint;

    private $protocol;

    private $insecure;
    
    private $certificateFile;

    private $headers;

    private $compression;

    private $timeout;



    /**
     * Constructor.
     * 
     * @param array $opts
     */
    public function __construct(
        array $opts
    )
    {
        $this->endpoint = getenv('OTEL_EXPORTER_OTLP_ENDPOINT') ?: $this->endpoint;
        $this->protocol = getenv('OTEL_EXPORTER_OTLP_PROTOCOL') ?: 'grpc';
        $this->insecure = getenv('OTEL_EXPORTER_OTLP_INSECURE') ? filter_var(getenv('OTEL_EXPORTER_OTLP_INSECURE'), FILTER_VALIDATE_BOOLEAN): $this->insecure;
        $this->certificateFile = getenv('OTEL_EXPORTER_OTLP_CERTIFICATE') ?: $this->certificateFile;
        $this->headers = getenv('OTEL_EXPORTER_OTLP_HEADERS') ?: $this->headers;
        $this->compression = getenv('OTEL_EXPORTER_OTLP_COMPRESSION') ?: $this->compression;
        $this->timeout =(int) getenv('OTEL_EXPORTER_OTLP_TIMEOUT') ?: $this->timeout;
    }

    public function WithEndpoint(string $endpoint)
    {
        $parsedDsn = parse_url($endpoint);

        if (!is_array($parsedDsn)) {
            throw new InvalidArgumentException('Unable to parse provided DSN');
        }
    
        if (
            !isset($parsedDsn['scheme'])
            || !isset($parsedDsn['host'])
            || !isset($parsedDsn['port'])
            || !isset($parsedDsn['path'])
        ) {
            throw new InvalidArgumentException('Endpoint should have scheme, host, port and path');
        }

        $this->endpoint = $endpoint;

        return $this;

    }

    #TODO: Endpoint path for http should be possible
    public function WithProtocol(string $protocol)
    {
        if ($protocol != 'http/protobuf') {
            throw new InvalidArgumentException('Invalid OTLP Protocol Specified');
        }

        $this->protocol = $protocol;

        return $this;
    }

    public function WithHeaders(string $headers)
    {

        if (empty($headers)) {
            return [];
        }

        $pairs = explode(',', $headers);

        $metadata = [];
        foreach ($pairs as $pair) {
            $kv = explode('=', $pair, 2);

            if (count($kv) !== 2) {
                throw new InvalidArgumentException('Invalid headers passed');
            }

            list($key, $value) = $kv;

            $metadata[$key] = $value;
        }

        $this->headers = $metadata;

        return $this;

    }

    public function WithCompression()
    {
        $this->compression = 'gzip';

        return $this;

    }

    public function WithTimeout(int $timeout)
    {
        $this->timeout = $timeout;

        return $this;

    }

    public function WithInsecure()
    {
        $this->insecure = true;

        return $this;

    }
}

/*

Notes:
otel-go on splitting http/grpc
https://github.com/open-telemetry/opentelemetry-go/issues/1085#issuecomment-808438423

*/