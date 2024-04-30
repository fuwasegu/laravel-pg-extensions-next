<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Tests\Functional\Schema;

use Fuwasegu\Postgres\Schema\Blueprint;
use Fuwasegu\Postgres\Tests\FunctionalTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Override;

/**
 * @internal
 *
 * @coversNothing
 */
final class GetForeignKeysTest extends FunctionalTestCase
{
    use DatabaseTransactions;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_foreign_table1', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
        });

        Schema::create('test_foreign_table2', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
        });

        Schema::create('test_table', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->bigInteger('test1_id');
            $table->bigInteger('test2_id');
            $table->foreign(['test1_id'])->references('id')->on('test_foreign_table1');
            $table->foreign(['test2_id'])->references('id')->on('test_foreign_table2');
        });
    }

    #[Override]
    protected function tearDown(): void
    {
        Schema::dropIfExists('test_table');
        Schema::dropIfExists('test_foreign_table1');
        Schema::dropIfExists('test_foreign_table2');

        parent::tearDown();
    }

    public function testGetForeignKeys(): void
    {
        $foreignKeys = Schema::getForeignKeys('test_table');

        $this->assertSame(
            (array)$foreignKeys[0],
            [
                'source_column_name' => 'test1_id',
                'target_table_name' => 'test_foreign_table1',
                'target_column_name' => 'id',
            ],
        );

        $this->assertSame(
            (array)$foreignKeys[1],
            [
                'source_column_name' => 'test2_id',
                'target_table_name' => 'test_foreign_table2',
                'target_column_name' => 'id',
            ],
        );
    }
}
