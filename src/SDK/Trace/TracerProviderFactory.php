<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Event\Dispatcher;
use OpenTelemetry\SDK\Common\Event\Event\WarningEvent;

final class TracerProviderFactory
{
    use LogsMessagesTrait;

    private ExporterFactory $exporterFactory;
    private SamplerFactory $samplerFactory;
    private SpanProcessorFactory $spanProcessorFactory;

    public function __construct(
        string $name,
        ?ExporterFactory $exporterFactory = null,
        ?SamplerFactory $samplerFactory = null,
        ?SpanProcessorFactory $spanProcessorFactory = null
    ) {
        $this->exporterFactory = $exporterFactory ?: new ExporterFactory($name);
        $this->samplerFactory = $samplerFactory ?: new SamplerFactory();
        $this->spanProcessorFactory = $spanProcessorFactory ?: new SpanProcessorFactory();
    }

    public function create(): API\TracerProviderInterface
    {
        try {
            $exporter = $this->exporterFactory->fromEnvironment();
        } catch (\Throwable $t) {
            Dispatcher::getInstance()->dispatch(new WarningEvent('Unable to create exporter', $t));
            $exporter = null;
        }

        try {
            $sampler = $this->samplerFactory->fromEnvironment();
        } catch (\Throwable $t) {
            Dispatcher::getInstance()->dispatch(new WarningEvent('Unable to create sampler', $t));
            $sampler = null;
        }

        try {
            $spanProcessor = $this->spanProcessorFactory->fromEnvironment($exporter);
        } catch (\Throwable $t) {
            Dispatcher::getInstance()->dispatch(new WarningEvent('Unable to create span processor', $t));
            $spanProcessor = null;
        }

        return new TracerProvider(
            $spanProcessor,
            $sampler,
        );
    }
}
