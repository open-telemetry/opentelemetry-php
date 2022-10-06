<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\SDK\Resource\Detectors\Sdk;
use OpenTelemetry\SemConv\ResourceAttributes;

class OtlpUtil
{
    /**
     * @link https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/protocol/exporter.md#user-agent
     */
    public static function getUserAgentHeader(): array
    {
        $resource = (new Sdk())->getResource();

        return ['User-Agent' => sprintf(
            'OTel OTLP Exporter PHP/%s',
            $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION) ?: 'unknown'
        )];
    }
}
