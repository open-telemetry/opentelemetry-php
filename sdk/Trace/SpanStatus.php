<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

final class SpanStatus implements API\SpanStatus
{

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $description;

    public function __construct(string $code = self::UNSET, string $description = null)
    {
        /*
        * Description provides a descriptive message of the Status. Description MUST
        * only be used with the Error StatusCode value.
        * Description MUST be IGNORED for StatusCode Ok & Unset values.
        * If an invalid code is provided, the default span is set.
        */

        if (!array_key_exists($code, self::DESCRIPTION)) {
            // An invalid code was given; return default spanStatus.
            $code = self::UNSET;
            $description = self::DESCRIPTION[self::UNSET];
        }
        if ((!$description) || ($code != self::ERROR)) {
            $description = self::DESCRIPTION[$code];
        }
        $this->code = $code;
        $this->description = $description;
    }

    public static function new(string $code = self::UNSET, string $description = null): SpanStatus
    {
        /*
        * Description provides a descriptive message of the Status. Description MUST
        * only be used with the Error StatusCode value.
        * Description MUST be IGNORED for StatusCode Ok & Unset values.
        * If an invalid code is provided, the default span is returned.
        */

        if (!array_key_exists($code, self::DESCRIPTION)) {
            // An invalid code was given; return default spanStatus.
            return new SpanStatus();
        }

        if ((!$description) || ($code != self::ERROR)) {
            $description = self::DESCRIPTION[$code];
        }

        return new SpanStatus($code, $description);
    }

    public static function ok(): SpanStatus
    {
        return self::new(self::OK);
    }

    public function getCanonicalStatusCode(): string
    {
        return $this->code;
    }

    public function getStatusDescription(): string
    {
        return $this->description;
    }

    public function isStatusOK() : bool
    {
        return $this->code === self::OK;
    }
    public function setStatus(string $code = self::UNSET, string $description = null): void
    {
        /*
        * Description provides a descriptive message of the Status. Description MUST
        * only be used with the Error StatusCode value.
        * Description MUST be IGNORED for StatusCode Ok & Unset values.
        * If an invalid code is provided, the default span is set.
        */

        if (!array_key_exists($code, self::DESCRIPTION)) {
            // An invalid code was given; set to defaults.
            $this->code = self::UNSET;
            $this->description = self::DESCRIPTION[self::UNSET];

            return;
        }

        if ((!$description) || ($code != self::ERROR)) {
            $description = self::DESCRIPTION[$code];
        }
        $this->code = $code;
        $this->description = $description;
    }
}
