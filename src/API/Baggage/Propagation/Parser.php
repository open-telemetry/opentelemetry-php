<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Baggage\Propagation;

use function explode;
use OpenTelemetry\API\Baggage\BaggageBuilderInterface;
use OpenTelemetry\API\Baggage\Metadata;
use OpenTelemetry\SDK\Internal\StringUtil;
use function str_replace;
use function trim;
use function urldecode;

final class Parser
{
    private const EXCLUDED_KEY_CHARS = [' ', '(', ')', '<', '>', '@', ',', ';', ':', '\\', '"', '/', '[', ']', '?', '=', '{', '}'];
    private const EXCLUDED_VALUE_CHARS = [' ', '"', ',', ';', '\\'];

    /** @readonly */
    private string $baggageHeader;

    public function __construct(string $baggageHeader)
    {
        $this->baggageHeader = $baggageHeader;
    }

    /**
     * @todo: Make this algorithm more robust/performant.
     */
    public function parseInto(BaggageBuilderInterface $baggageBuilder): void
    {
        foreach (explode(',', $this->baggageHeader) as $baggageString) {
            if (empty(trim($baggageString))) {
                continue;
            }

            $explodedString = explode(';', $baggageString, 2);

            $keyValue = trim($explodedString[0]);

            if (empty($keyValue) || !StringUtil::str_contains($keyValue, '=')) {
                continue;
            }

            $metadataString = $explodedString[1] ?? null;

            if ($metadataString && !empty(trim(($metadataString)))) {
                $metadata = new Metadata(trim($metadataString));
            } else {
                $metadata = null;
            }

            [$key, $value] = explode('=', $keyValue, 2);

            $key = urldecode($key);
            $value = urldecode($value);

            $key = str_replace(self::EXCLUDED_KEY_CHARS, '', trim($key), $invalidKeyCharacters);
            if (empty($key) || $invalidKeyCharacters > 0) {
                continue;
            }

            $value = str_replace(self::EXCLUDED_VALUE_CHARS, '', trim($value), $invalidValueCharacters);
            if (empty($value) || $invalidValueCharacters > 0) {
                continue;
            }

            $baggageBuilder->set($key, $value, $metadata);
        }
    }
}
