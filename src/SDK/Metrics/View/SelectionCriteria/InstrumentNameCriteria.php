<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

use OpenTelemetry\SDK\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria;
use function preg_match;
use function preg_quote;
use function sprintf;
use function strtr;

final class InstrumentNameCriteria implements SelectionCriteria
{
    private string $pattern;

    public function __construct(string $name)
    {
        $this->pattern = sprintf('/^%s$/', strtr(preg_quote($name, '/'), ['\\?' => '.', '\\*' => '.*']));
    }

    public function accepts(Instrument $instrument, InstrumentationScope $instrumentationScope): bool
    {
        return !!preg_match($this->pattern, $instrument->name);
    }
}
