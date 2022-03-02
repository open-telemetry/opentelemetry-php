<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Subscriber\Event;

use function count;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\AttributesInterface;
use OpenTelemetry\SDK\Trace;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Subscriber\EventInterface;

Class StartSpanEvent implements EventInterface
{
	private Span $span;
	public function __construct(Span $span)
	{
		$this->span = $span;
	}

	public function getObject():Span
	{
		return $this->span;
	}

}	