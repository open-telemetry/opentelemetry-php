<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;

/**
 * @internal
 */
final class TreatNullAsUnsetNormalization
{

    public function apply(ArrayNodeDefinition $root): void
    {
        foreach ($root->getChildNodeDefinitions() as $childNode) {
            $this->doApply($childNode);
        }
    }

    private function doApply(NodeDefinition $node): void
    {
        $node->beforeNormalization()->ifNull()->thenUnset()->end();

        if ($node instanceof ParentNodeDefinitionInterface) {
            foreach ($node->getChildNodeDefinitions() as $childNode) {
                $this->doApply($childNode);
            }
        }
    }
}
