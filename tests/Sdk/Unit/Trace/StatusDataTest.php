<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\StatusData;
use OpenTelemetry\Trace\StatusCode;
use PHPUnit\Framework\TestCase;

class StatusDataTest extends TestCase
{
    /**
     * @dataProvider getStatuses
     *
     * @param StatusCode::STATUS_* $code
     */
    public function test_statuses(string $code): void
    {
        $status = StatusData::create($code, '');

        switch ($code) {
            case StatusCode::STATUS_ERROR:
                $this->assertEquals(StatusData::error(), $status);

                break;
            case StatusCode::STATUS_OK:
                $this->assertEquals(StatusData::ok(), $status);

                break;
            case StatusCode::STATUS_UNSET:
                $this->assertEquals(StatusData::unset(), $status);

                break;
        }
    }

    /**
     * @dataProvider getStatuses
     *
     * @param StatusCode::STATUS_* $code
     */
    public function test_statuses_description(string $code): void
    {
        $status = StatusData::create($code, 'ERR');

        switch ($code) {
            case StatusCode::STATUS_ERROR:
                $this->assertSame(StatusCode::STATUS_ERROR, $status->getCode());
                $this->assertSame('ERR', $status->getDescription());

                break;
            case StatusCode::STATUS_OK:
                $this->assertEquals(StatusData::ok(), $status);

                break;
            case StatusCode::STATUS_UNSET:
                $this->assertEquals(StatusData::unset(), $status);

                break;
        }

        $this->assertSame($code, $status->getCode());
    }

    public function getStatuses(): array
    {
        return [
            [StatusCode::STATUS_ERROR],
            [StatusCode::STATUS_OK],
            [StatusCode::STATUS_UNSET],
        ];
    }
}
