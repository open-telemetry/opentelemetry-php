<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Subscriber\Listener;

use function count;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\AttributesInterface;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Subscriber\ListenerInterface;

class StartSpanListener implements ListenerInterface
{

	public function action(Span $span):void
	{
		$span->setAttribute('Listener','Does_some_action');

	}
}