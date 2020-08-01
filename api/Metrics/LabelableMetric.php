<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

interface LabelableMetric extends Metric
{
    /**
     * Get $labels
     * todo: we will probably need a class Label and a typed collection for labels
     *
     * @return  array<string>
     */
    public function getLabels(): array;

    /**
     * Set $labels
     *
     * @param  array<string>  $labels
     * @return  self
     */
    public function setLabels(array $labels);

    /**
     * Set $labels
     *
     * @param  string  $label
     * @return  self
     */
    public function addLabel(string $label);
}
