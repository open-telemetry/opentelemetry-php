<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use InvalidArgumentException;
use OpenTelemetry\SDK\EnvironmentVariablesTrait;

class ConfigOpts
{
    use EnvironmentVariablesTrait;

    private ?string $endpoint = null;
    private ?string $protocol = null;
    private ?array $headers = null;
    private ?bool $insecure = null;
    private ?string $certificateFile = null;
    private ?string $compression = null;
    private ?int $timeout = null;

    public function withEndpoint(string $endpoint)
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function withProtocol(string $protocol): self
    {
        $this->protocol = $protocol;

        return $this;
    }

    public function withHeaders(string $headers): self
    {
        $this->headers = $this->extractHeaders($headers);

        return $this;
    }

    private function extractHeaders(string $headers): array
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

        return $metadata;
    }

    public function withCompression(string $compression = 'gzip'): self
    {
        $this->compression = $compression;

        return $this;
    }

    public function withTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function withInsecure(): self
    {
        $this->insecure = true;

        return $this;
    }

    public function withSecure(): self
    {
        $this->insecure = false;

        return $this;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint ?: getenv('OTEL_EXPORTER_OTLP_ENDPOINT') ?: getenv('OTEL_EXPORTER_OTLP_TRACES_ENDPOINT') ?: 'http://localhost:4318';
    }

    public function getProtocol(): string
    {
        return $this->protocol ?: getenv('OTEL_EXPORTER_OTLP_PROTOCOL') ?: getenv('OTEL_EXPORTER_OTLP_TRACES_PROTOCOL') ?: 'http/protobuf';
    }

    public function getHeaders(): array
    {
        return $this->headers ?: $this->extractHeaders(
            getenv('OTEL_EXPORTER_OTLP_HEADERS') ?: getenv('OTEL_EXPORTER_OTLP_TRACES_HEADERS') ?: ''
        );
    }

    public function getInsecure(): bool
    {
        //only applies to grpc
        return $this->insecure ?: $this->getBooleanFromEnvironment(
            'OTEL_EXPORTER_OTLP_INSECURE',
            $this->getBooleanFromEnvironment('OTEL_EXPORTER_OTLP_SPAN_INSECURE', false)
        );
    }

    public function getCertificateFile(): string
    {
        return $this->certificateFile ?: getenv('OTEL_EXPORTER_OTLP_CERTIFICATE') ?: getenv('OTLP_EXPORTER_OTLP_TRACES_CERTIFICATE') ?: '';
    }

    public function getCompression(): string
    {
        return $this->compression ?: getenv('OTEL_EXPORTER_OTLP_COMPRESSION') ?: getenv('OTEL_EXPORTER_OTLP_TRACES_COMPRESSION') ?: 'none';
    }

    public function getTimeout(): int
    {
        return $this->timeout ?: $this->getIntFromEnvironment(
            'OTEL_EXPORTER_OTLP_TIMEOUT',
            $this->getIntFromEnvironment('OTEL_EXPORTER_OTLP_TRACES_TIMEOUT', 10)
        );
    }
}

/*

Notes:
otel-go on splitting http/grpc
https://github.com/open-telemetry/opentelemetry-go/issues/1085#issuecomment-808438423

*/
