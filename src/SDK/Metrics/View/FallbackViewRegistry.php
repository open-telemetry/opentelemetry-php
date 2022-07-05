<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\View;

use OpenTelemetry\SDK\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\View;
use OpenTelemetry\SDK\Metrics\ViewRegistry;

final class FallbackViewRegistry implements ViewRegistry {

    private ViewRegistry $views;
    private iterable $fallback;

    /**
     * @param iterable<ViewTemplate> $fallback
     */
    public function __construct(ViewRegistry $views, iterable $fallback) {
        $this->views = $views;
        $this->fallback = $fallback;
    }

    public function find(Instrument $instrument, InstrumentationScope $instrumentationScope): iterable {
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
