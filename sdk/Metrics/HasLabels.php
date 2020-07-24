<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics;

trait HasLabels
{
    /**
     * @var array $labels
     */
    protected $labels = [];

    /**
     * {@inheritDoc}
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * {@inheritDoc}
     */
    public function setLabels(array $labels)
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addLabel(string $label)
    {
        $this->labels[] = $label;

        return $this;
    }
}
