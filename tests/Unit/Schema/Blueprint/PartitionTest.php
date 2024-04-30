<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Unit\Schema\Blueprint;

use Fuwasegu\Postgres\Tests\TestCase;
use Fuwasegu\Postgres\Tests\Unit\Helpers\BlueprintAssertions;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Override;

/**
 * @internal
 *
 * @coversNothing
 */
final class PartitionTest extends TestCase
{
    use BlueprintAssertions;

    private const TABLE = 'test_table';

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeMock(self::TABLE);
    }

    public function testDetachPartition(): void
    {
        $this->blueprint->detachPartition('some_partition');
        $this->assertSameSql('alter table "test_table" detach partition some_partition');
    }

    public function testAttachPartitionRangeInt(): void
    {
        $this->blueprint->attachPartition('some_partition')
            ->range([
                'from' => 10,
                'to' => 100,
            ]);
        $this->assertSameSql('alter table "test_table" attach partition some_partition for values from (10) to (100)');
    }

    public function testAttachPartitionFailedWithoutForValuesPart(): void
    {
        $this->blueprint->attachPartition('some_partition');
        $this->expectException(InvalidArgumentException::class);
        $this->runToSql();
    }

    public function testAttachPartitionRangeDates(): void
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        $this->blueprint->attachPartition('some_partition')
            ->range([
                'from' => $today,
                'to' => $tomorrow,
            ]);

        $this->assertSameSql(sprintf(
            'alter table "test_table" attach partition some_partition for values from (\'%s\') to (\'%s\')',
            $today->toDateTimeString(),
            $tomorrow->toDateTimeString(),
        ));
    }

    public function testAttachPartitionStringDates(): void
    {
        $today = '2010-01-01';
        $tomorrow = '2010-12-31';
        $this->blueprint->attachPartition('some_partition')
            ->range([
                'from' => $today,
                'to' => $tomorrow,
            ]);

        $this->assertSameSql(sprintf(
            'alter table "test_table" attach partition some_partition for values from (\'%s\') to (\'%s\')',
            $today,
            $tomorrow,
        ));
    }

    public function testAddingTsrangeColumn(): void
    {
        $this->blueprint->tsrange('foo');
        $this->assertSameSql('alter table "test_table" add column "foo" tsrange not null');
    }

    public function testAddingTstzrangeColumn(): void
    {
        $this->blueprint->tstzrange('foo');
        $this->assertSameSql('alter table "test_table" add column "foo" tstzrange not null');
    }

    public function testAddingDaterangeColumn(): void
    {
        $this->blueprint->daterange('foo');
        $this->assertSameSql('alter table "test_table" add column "foo" daterange not null');
    }

    public function testAddingNumericColumnWithVariablePrecicion(): void
    {
        $this->blueprint->numeric('foo');
        $this->assertSameSql('alter table "test_table" add column "foo" numeric not null');
    }

    public function testAddingNumericColumnWithDefinedPrecicion(): void
    {
        $this->blueprint->numeric('foo', 8);
        $this->assertSameSql('alter table "test_table" add column "foo" numeric(8) not null');
    }

    public function testAddingNumericColumnWithDefinedPrecicionAndScope(): void
    {
        $this->blueprint->numeric('foo', 8, 2);
        $this->assertSameSql('alter table "test_table" add column "foo" numeric(8, 2) not null');
    }
}
