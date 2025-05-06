<?php

declare(strict_types=1);

require_once DIRNAME(__DIR__, 3) . '/vendor/autoload.php';

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\SpanSuppression;

/**
 * Span suppression is a feature used by Instrumentation Libraries to eliminate redundant child spans. For example,
 * multiple HTTP client implementations (a 3rd-party SDK client, which uses Guzzle, which uses cURL) may create a
 * span for each of the HTTP calls. This can lead to multiple nested CLIENT spans being created.
 */

$scopes = [];

function check(): void
{
    echo 'Should suppress SERVER span: ' . (SpanSuppression::shouldSuppress(SpanKind::KIND_SERVER) ? 'Yes' : 'No') . PHP_EOL;
    echo 'Should suppress CLIENT span: ' . (SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT) ? 'Yes' : 'No') . PHP_EOL;
    echo 'Should suppress CONSUMER span: ' . (SpanSuppression::shouldSuppress(SpanKind::KIND_CONSUMER) ? 'Yes' : 'No') . PHP_EOL;
    echo 'Should suppress PRODUCER span: ' . (SpanSuppression::shouldSuppress(SpanKind::KIND_PRODUCER) ? 'Yes' : 'No') . PHP_EOL;
    echo 'Should suppress INTERNAL span: ' . (SpanSuppression::shouldSuppress(SpanKind::KIND_INTERNAL) ? 'Yes' : 'No') . PHP_EOL;
}

// initially, no suppression
echo "\nInitial state:\n";
check();

$scopes[] = SpanSuppression::suppressSpanKind([SpanKind::KIND_SERVER])->activate();

// add suppression of SERVER spans
echo "\nWith SERVER suppression:\n";

check();

$scopes[] = SpanSuppression::suppressSpanKind([
    SpanKind::KIND_CLIENT,
    SpanKind::KIND_CONSUMER,
])->activate();

// add suppression of CLIENT and CONSUMER spans, which should be additive
echo "\nWith CLIENT+CONSUMER suppression added:\n";

check();

//detach active suppression, leaving SERVER
array_pop($scopes)->detach();

echo "\nWith CLIENT+CONSUMER suppression detached:\n";

check();

// detach active, leaving default (none) suppression
array_pop($scopes)->detach();

echo "\nWith SERVER suppression detached:\n";

check();
