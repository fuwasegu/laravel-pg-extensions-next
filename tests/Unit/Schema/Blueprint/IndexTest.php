<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Unit\Schema\Blueprint;

use Closure;
use Fuwasegu\Postgres\Schema\Blueprint;
use Fuwasegu\Postgres\Tests\TestCase;
use Fuwasegu\Postgres\Tests\Unit\Helpers\BlueprintAssertions;
use Override;

/**
 * @internal
 *
 * @coversNothing
 */
final class IndexTest extends TestCase
{
    use BlueprintAssertions;

    private const TABLE = 'test_table';

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeMock(self::TABLE);
    }

    /**
     * @dataProvider provideAddConstraintCases
     */
    public function testAddConstraint(Closure $callback, string $expectedSQL): void
    {
        $callback($this->blueprint);
        $this->assertSameSql($expectedSQL);
    }

    public static function provideAddConstraintCases(): iterable
    {
        yield [
            static function (Blueprint $table): void {
                $table
                    ->exclude(['period_start', 'period_end'])
                    ->using('period_type_id', '=')
                    ->using('daterange(period_start, period_end)', '&&')
                    ->method('gist')
                    ->whereNull('deleted_at');
            },
            implode(' ', [
                'ALTER TABLE test_table ADD CONSTRAINT test_table_period_start_period_end_excl',
                'EXCLUDE USING gist (period_type_id WITH =, daterange(period_start, period_end) WITH &&)',
                'WHERE ("deleted_at" is null)',
            ]),
        ];

        yield [
            static function (Blueprint $table): void {
                $table
                    ->exclude(['period_start', 'period_end'])
                    ->using('period_type_id', '=')
                    ->using('daterange(period_start, period_end)', '&&')
                    ->whereNull('deleted_at');
            },
            implode(' ', [
                'ALTER TABLE test_table ADD CONSTRAINT test_table_period_start_period_end_excl',
                'EXCLUDE (period_type_id WITH =, daterange(period_start, period_end) WITH &&)',
                'WHERE ("deleted_at" is null)',
            ]),
        ];

        yield [
            static function (Blueprint $table): void {
                $table
                    ->exclude(['period_start', 'period_end'])
                    ->using('period_type_id', '=')
                    ->using('daterange(period_start, period_end)', '&&');
            },
            implode(' ', [
                'ALTER TABLE test_table ADD CONSTRAINT test_table_period_start_period_end_excl',
                'EXCLUDE (period_type_id WITH =, daterange(period_start, period_end) WITH &&)',
            ]),
        ];

        yield [
            static function (Blueprint $table): void {
                $table
                    ->exclude(['period_start', 'period_end'])
                    ->using('period_type_id', '=')
                    ->using('daterange(period_start, period_end)', '&&')
                    ->tableSpace('excludeSpace');
            },
            implode(' ', [
                'ALTER TABLE test_table ADD CONSTRAINT test_table_period_start_period_end_excl',
                'EXCLUDE (period_type_id WITH =, daterange(period_start, period_end) WITH &&)',
                'USING INDEX TABLESPACE excludeSpace',
            ]),
        ];

        yield [
            static function (Blueprint $table): void {
                $table
                    ->exclude(['period_start', 'period_end'])
                    ->using('period_type_id', '=')
                    ->using('daterange(period_start, period_end)', '&&')
                    ->with('some_arg', 1)
                    ->with('any_arg', 'some_value');
            },
            implode(' ', [
                'ALTER TABLE test_table ADD CONSTRAINT test_table_period_start_period_end_excl',
                'EXCLUDE (period_type_id WITH =, daterange(period_start, period_end) WITH &&)',
                "WITH (some_arg = 1, any_arg = 'some_value')",
            ]),
        ];

        yield [
            static function (Blueprint $table): void {
                $table
                    ->check(['period_start', 'period_end'])
                    ->whereColumn('period_end', '>', 'period_start')
                    ->whereRaw('period_start NOT NULL or period_end NOT NULL');
            },
            implode(' ', [
                'ALTER TABLE test_table ADD CONSTRAINT test_table_period_start_period_end_chk',
                'CHECK (("period_end" > "period_start") and (period_start NOT NULL or period_end NOT NULL))',
            ]),
        ];
    }
}
