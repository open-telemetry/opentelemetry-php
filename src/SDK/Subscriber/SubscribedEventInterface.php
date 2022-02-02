<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Subscriber;

use function count;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\AttributesInterface;
use OpenTelemetry\SDK\ListenerInterface as ListenerInterface;
use OpenTelemetry\SDK\Trace\Span;

interface SubscribedEventInterface
{

	public function getSpan(Span $span): Span;
}