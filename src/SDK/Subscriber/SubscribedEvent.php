<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Subscriber;

use function count;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\AttributesInterface;
use OpenTelemetry\SDK\Trace;
use OpenTelemetry\SDK\Trace\Span;

final class SubscribedEvent implements SubscribedEventInterface
{
	
	private Span $span;

	public function __construct(Span $span)
	{
		$this->span = $span;
	}

	public function dispatch():void
	{
		$classname = get_called_class();
		$classname = str_replace('OpenTelemetry\\SDK\\Subscriber\\','',$classname);
		$listenerClassName = $this->span->getListener($classname);
		$listenerClassName = "OpenTelemetry\\SDK\\Subscriber\\Listener\\".$listenerClassName;
		$listener = new $listenerClassName();
		$listener->action($this->span);

	}

	public function getSpan(Span $span):Span
	{
		return $this->span;
	}
}	