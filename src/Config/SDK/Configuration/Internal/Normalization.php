<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use Symfony\Component\Config\Definition\NodeInterface;

/**
 * @internal
 */
interface Normalization
{
    /**
     * Applied from {@see \Symfony\Component\Config\Definition\BaseNode::preNormalize()}, which runs
     * before the node's `beforeNormalization()` closures, so that those closures see normalized
     * values.
     */
    public function applyToNode(NodeInterface $node, mixed $value): mixed;
}
