<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\ViewRegistryInterface;

final class FallbackViewRegistry implements ViewRegistryInterface
{
    private ViewRegistryInterface $views;
    private iterable $fallback;

    /**
     * @param iterable<ViewTemplate> $fallback
     */
    public function __construct(ViewRegistryInterface $views, iterable $fallback)
    {
        $this->views = $views;
        $this->fallback = $fallback;
    }

    public function find(Instrument $instrument, InstrumentationScopeInterface $instrumentationScope): iterable
    {
        $fallback = true;
        foreach ($this->views->find($instrument, $instrumentationScope) as $projection) {
            yield $projection;

            $fallback = false;
        }

        if ($fallback) {
            foreach ($this->fallback as $view) {
                yield $view->project($instrument);
            }
        }
    }
}
