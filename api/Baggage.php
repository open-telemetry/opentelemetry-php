<?php

declare(strict_types=1);

namespace OpenTelemetry;

use OpenTelemetry\Context\ContextKey;

interface Baggage
{
    public function getCorrelations(); // TODO

    public function get(ContextKey $key);

    public static function getValue(ContextKey $key, ?Baggage $ctx);

    public function set(ContextKey $key, $value): Baggage;

    public static function setValue(ContextKey $key, $value, ?Baggage $parent = null): Baggage;

    public function removeCorrelation(): Baggage;

    public function clearCorrelations(); // TODO
}
