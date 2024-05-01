<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Tests\Unit\Schema\Blueprint;

use Fuwasegu\Postgres\Tests\TestCase;
use Fuwasegu\Postgres\Tests\Unit\Helpers\BlueprintAssertions;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\Test;

final class PartitionTest extends TestCase
{
    use BlueprintAssertions;

    private const string TABLE = 'test_table';

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeMock(self::TABLE);
    }

    #[Test]
    public function detachPartition(): void
    {
        $this->blueprint->detachPartition('some_partition');
        $this->assertSameSql('alter table "test_table" detach partition some_partition');
    }

    #[Test]
    public function attachPartitionRangeInt(): void
    {
        $this->blueprint->attachPartition('some_partition')
            ->range([
                'from' => 10,
                'to' => 100,
            ]);
        $this->assertSameSql('alter table "test_table" attach partition some_partition for values from (10) to (100)');
    }

    #[Test]
    public function attachPartitionFailedWithoutForValuesPart(): void
    {
        $this->blueprint->attachPartition('some_partition');
        $this->expectException(InvalidArgumentException::class);
        $this->runToSql();
    }

    #[Test]
    public function attachPartitionRangeDates(): void
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

    #[Test]
    public function attachPartitionStringDates(): void
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

    #[Test]
    public function addingTsrangeColumn(): void
    {
        $this->blueprint->tsrange('foo');
        $this->assertSameSql('alter table "test_table" add column "foo" tsrange not null');
    }

    #[Test]
    public function addingTstzrangeColumn(): void
    {
        $this->blueprint->tstzrange('foo');
        $this->assertSameSql('alter table "test_table" add column "foo" tstzrange not null');
    }

    #[Test]
    public function addingDaterangeColumn(): void
    {
        $this->blueprint->daterange('foo');
        $this->assertSameSql('alter table "test_table" add column "foo" daterange not null');
    }

    #[Test]
    public function addingNumericColumnWithVariablePrecicion(): void
    {
        $this->blueprint->numeric('foo');
        $this->assertSameSql('alter table "test_table" add column "foo" numeric not null');
    }

    #[Test]
    public function addingNumericColumnWithDefinedPrecicion(): void
    {
        $this->blueprint->numeric('foo', 8);
        $this->assertSameSql('alter table "test_table" add column "foo" numeric(8) not null');
    }

    #[Test]
    public function addingNumericColumnWithDefinedPrecicionAndScope(): void
    {
        $this->blueprint->numeric('foo', 8, 2);
        $this->assertSameSql('alter table "test_table" add column "foo" numeric(8, 2) not null');
    }
}
