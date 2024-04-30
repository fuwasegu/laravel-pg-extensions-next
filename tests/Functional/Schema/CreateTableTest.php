<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Tests\Functional\Schema;

use Fuwasegu\Postgres\Helpers\ColumnAssertions;
use Fuwasegu\Postgres\Helpers\TableAssertions;
use Fuwasegu\Postgres\Schema\Blueprint;
use Fuwasegu\Postgres\Tests\FunctionalTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

/**
 * @internal
 *
 * @coversNothing
 */
final class CreateTableTest extends FunctionalTestCase
{
    use ColumnAssertions;

    use DatabaseTransactions;

    use TableAssertions;

    public function testCreateSimple(): void
    {
        Schema::create('test_table', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('field_comment')
                ->comment('test');
            $table->integer('field_default')
                ->default(123);
        });

        $this->seeTable('test_table');
    }

    public function testColumnAssertions(): void
    {
        Schema::create('test_table', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('field_comment')
                ->comment('test');
            $table->integer('field_default')
                ->default(123);
        });

        $this->assertSameTable(['id', 'name', 'field_comment', 'field_default'], 'test_table');

        $this->assertPostgresTypeColumn('test_table', 'id', 'integer');
        $this->assertLaravelTypeColumn('test_table', 'name', 'string');
        $this->assertPostgresTypeColumn('test_table', 'name', 'character varying');

        $this->assertDefaultOnColumn('test_table', 'field_default', '123');
        $this->assertCommentOnColumn('test_table', 'field_comment', 'test');

        $this->assertDefaultOnColumn('test_table', 'name');
        $this->assertCommentOnColumn('test_table', 'name');
    }

    public function testCreateViaLike(): void
    {
        Schema::create('test_table', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
        });

        Schema::create('test_table2', static function (Blueprint $table): void {
            $table->like('test_table');
        });

        $this->seeTable('test_table');
        $this->seeTable('test_table2');
        $this->assertCompareTables('test_table', 'test_table2');
    }

    public function testCreateViaLikeIncludingAll(): void
    {
        Schema::create('test_table', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name')
                ->unique();
        });

        Schema::create('test_table2', static function (Blueprint $table): void {
            $table->like('test_table')
                ->includingAll();
            $table->ifNotExists();
        });

        $this->seeTable('test_table');
        $this->seeTable('test_table2');
        $this->assertCompareTables('test_table', 'test_table2');
    }
}
