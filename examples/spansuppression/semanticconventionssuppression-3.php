<?php

declare(strict_types=1);

use OpenTelemetry\API\Common\Time\SystemClock;
use OpenTelemetry\API\Instrumentation\SpanSuppression\SemanticConvention;
use OpenTelemetry\API\Instrumentation\SpanSuppression\SemanticConventionSuppressionStrategy\SemanticConventionSuppressionStrategy;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProviderBuilder;

require_once __DIR__ . '/../../vendor/autoload.php';

// Nested Twig template rendering

$tp = (new TracerProviderBuilder())
    ->setResource(ResourceInfoFactory::emptyResource())
    ->addSpanProcessor(new BatchSpanProcessor(new SpanExporter(new StreamTransport(fopen('php://stdout', 'ab'), 'application/x-ndjson')), SystemClock::create()))
    ->setSpanSuppressionStrategy(new SemanticConventionSuppressionStrategy([
        new class() implements \OpenTelemetry\API\Instrumentation\SpanSuppression\SemanticConventionResolver {
            public function resolveSemanticConventions(string $name, ?string $version, ?string $schemaUrl): array
            {
                if ($name !== 'io.open-telemetry.php.twig') {
                    return [];
                }

                return [
                    new SemanticConvention('io.open-telemetry.php.twig.template.render', SpanKind::KIND_INTERNAL, ['twig.template.name'], []),
                ];
            }
        },
    ]))
    ->build()
;

$t = $tp->getTracer('io.open-telemetry.php.twig');
$c1 = $t->spanBuilder('render index.html.twig')->setAttribute('twig.template.name', 'index.html.twig')->startSpan();
$s1 = $c1->activate();

try {
    $c2 = $t->spanBuilder('render header.html.twig')->setAttribute('twig.template.name', 'header.html.twig')->startSpan();
    $s2 = $c2->activate();

    try {
        for ($i = 0; $i < 5; $i++) {
            $t->spanBuilder('render meta.html.twig')->setAttribute('twig.template.name', 'meta.html.twig')->startSpan()->end();
        }
        // ...
    } finally {
        $s2->detach();
        $c2->end();
    }
} finally {
    $s1->detach();
    $c1->end();
}

$tp->shutdown();
