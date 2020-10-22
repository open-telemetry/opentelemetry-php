<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics;

trait HasLabels
{
    /**
     * @var array<string>
     */
    protected $labels = [];

    /**
     * @return	array<string>
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
        foreach ($labels as $label) {
            if (! is_string($label)) {
                throw new \InvalidArgumentException('The label is expected to be a string');
            }
        }

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
