<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\SDK\Trace\StatusData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(StatusData::class)]
class StatusDataTest extends TestCase
{
    /**
     * @psalm-param StatusCode::STATUS_* $code
     */
    #[DataProvider('getStatuses')]
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
     * @psalm-param StatusCode::STATUS_* $code
     */
    #[DataProvider('getStatuses')]
    #[Group('trace-compliance')]
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

    public static function getStatuses(): array
    {
        return [
            [StatusCode::STATUS_ERROR],
            [StatusCode::STATUS_OK],
            [StatusCode::STATUS_UNSET],
        ];
    }

    public function test_create_with_description_ignores_description_for_ok(): void
    {
        $status = StatusData::create(StatusCode::STATUS_OK, 'some description');
        $this->assertSame(StatusCode::STATUS_OK, $status->getCode());
        $this->assertSame('', $status->getDescription());
    }

    public function test_create_with_description_ignores_description_for_unset(): void
    {
        $status = StatusData::create(StatusCode::STATUS_UNSET, 'some description');
        $this->assertSame(StatusCode::STATUS_UNSET, $status->getCode());
        $this->assertSame('', $status->getDescription());
    }

    public function test_create_with_null_description(): void
    {
        $status = StatusData::create(StatusCode::STATUS_ERROR);
        $this->assertSame(StatusCode::STATUS_ERROR, $status->getCode());
        $this->assertSame('', $status->getDescription());
    }

    public function test_ok_returns_same_instance(): void
    {
        $this->assertSame(StatusData::ok(), StatusData::ok());
    }

    public function test_error_returns_same_instance(): void
    {
        $this->assertSame(StatusData::error(), StatusData::error());
    }

    public function test_unset_returns_same_instance(): void
    {
        $this->assertSame(StatusData::unset(), StatusData::unset());
    }
}
