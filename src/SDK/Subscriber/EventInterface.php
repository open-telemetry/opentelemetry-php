<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Subscriber;

use function count;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\AttributesInterface;
use OpenTelemetry\SDK\Trace;
use OpenTelemetry\SDK\Trace\Span;

Interface EventInterface
{
	
	public function getObject():Span;

}	