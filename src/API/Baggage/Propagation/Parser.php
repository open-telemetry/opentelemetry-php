<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Baggage\Propagation;

use function explode;
use function mb_check_encoding;
use function mb_scrub;
use function mb_substitute_character;
use OpenTelemetry\API\Baggage\BaggageBuilderInterface;
use OpenTelemetry\API\Baggage\Metadata;
use function rawurldecode;
use function str_replace;
use function strlen;
use function trim;

final class Parser
{
    private const EXCLUDED_KEY_CHARS = [' ', '(', ')', '<', '>', '@', ',', ';', ':', '\\', '"', '/', '[', ']', '?', '=', '{', '}'];
    private const EXCLUDED_VALUE_CHARS = [' ', '"', ',', ';', '\\'];
    private const EQUALS = '=';

    /** @see https://www.w3.org/TR/baggage/#limits */
    private const MAX_BAGGAGE_BYTES = 8192;
    private const MAX_BAGGAGE_ENTRIES = 180;

    public function __construct(
        private readonly string $baggageHeader,
    ) {
    }

    public function parseInto(BaggageBuilderInterface $baggageBuilder): void
    {
        if (strlen($this->baggageHeader) > self::MAX_BAGGAGE_BYTES) {
            return;
        }

        // Counts only ACCEPTED entries (incremented at the bottom of the loop), not raw
        // comma-separated list-members, so empty/invalid pairs do not consume the W3C cap.
        $entries = 0;
        foreach (explode(',', $this->baggageHeader) as $baggageString) {
            if ($entries >= self::MAX_BAGGAGE_ENTRIES) {
                break;
            }
            if (empty(trim($baggageString))) {
                continue;
            }

            $explodedString = explode(';', $baggageString, 2);

            $keyValue = trim($explodedString[0]);

            if (empty($keyValue) || mb_strpos($keyValue, self::EQUALS) === false) {
                continue;
            }

            $metadataString = $explodedString[1] ?? null;

            if ($metadataString && !empty(trim(($metadataString)))) {
                $metadata = new Metadata(trim($metadataString));
            } else {
                $metadata = null;
            }

            /** @psalm-suppress PossiblyUndefinedArrayOffset */
            [$key, $value] = explode(self::EQUALS, $keyValue, 2);

            $key = str_replace(self::EXCLUDED_KEY_CHARS, '', trim($key), $invalidKeyCharacters);
            if (empty($key) || $invalidKeyCharacters > 0) {
                continue;
            }

            $value = str_replace(self::EXCLUDED_VALUE_CHARS, '', trim($value), $invalidValueCharacters);
            if (empty($value) || $invalidValueCharacters > 0) {
                continue;
            }

            // https://www.w3.org/TR/baggage/#value
            $value = rawurldecode($value);
            if (!mb_check_encoding($value, 'UTF-8')) {
                $substitute = mb_substitute_character();
                mb_substitute_character(0xFFFD);

                try {
                    $value = mb_scrub($value, 'UTF-8');
                } finally {
                    mb_substitute_character($substitute);
                }
            }

            $baggageBuilder->set($key, $value, $metadata);
            $entries++;
        }
    }
}
