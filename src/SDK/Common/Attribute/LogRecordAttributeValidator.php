<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

class LogRecordAttributeValidator implements AttributeValidatorInterface
{
    public function validate($value): bool
    {
        return true;
    }

    public function getInvalidMessage(): string
    {
        //not required as this validator always returns true
        return 'unused';
    }
}
