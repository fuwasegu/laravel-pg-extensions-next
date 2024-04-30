<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Tests\Functional\Connection;

use Fuwasegu\Postgres\Connectors\ConnectionFactory;
use Fuwasegu\Postgres\Schema\Blueprint;
use Fuwasegu\Postgres\Tests\_data\CustomSQLiteConnection;
use Fuwasegu\Postgres\Tests\FunctionalTestCase;
use Illuminate\Database\Connection;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class ConnectionTest extends FunctionalTestCase
{
    use DatabaseTransactions;

    use InteractsWithDatabase;

    protected $emulatePrepares = true;

    #[Test]
    public function connectionFactory(): void
    {
        $factory = new ConnectionFactory(app());

        $this->assertInstanceOf(SQLiteConnection::class, $factory->make(config('database.connections.sqlite')));
    }

    #[Test]
    public function resolverFor(): void
    {
        Connection::resolverFor('sqlite', static fn($connection, $database, $prefix, $config) => new CustomSQLiteConnection($connection, $database, $prefix, $config));

        $factory = new ConnectionFactory(app());

        $this->assertInstanceOf(
            CustomSQLiteConnection::class,
            $factory->make(config('database.connections.sqlite')),
        );
    }

    /**
     * @param mixed $value
     */
    #[DataProvider('provideBoolTrueBindingsWorksCases')]
    #[Test]
    public function boolTrueBindingsWorks($value): void
    {
        $table = 'test_table';
        $data = [
            'field' => $value,
        ];
        Schema::create($table, static function (Blueprint $table): void {
            $table->increments('id');
            $table->boolean('field');
        });
        DB::table($table)->insert($data);
        $result = DB::table($table)->select($data);
        $this->assertSame(1, $result->count());
    }

    /**
     * @param mixed $value
     */
    #[Test]
    #[DataProvider('provideIntBindingsWorksCases')]
    public function intBindingsWorks($value): void
    {
        $table = 'test_table';
        $data = [
            'field' => $value,
        ];
        Schema::create($table, static function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('field');
        });
        DB::table($table)->insert($data);
        $result = DB::table($table)->select($data);
        $this->assertSame(1, $result->count());
    }

    #[Test]
    public function stringBindingsWorks(): void
    {
        $table = 'test_table';
        $data = [
            'field' => 'string',
        ];
        Schema::create($table, static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('field');
        });
        DB::table($table)->insert($data);
        $result = DB::table($table)->select($data);
        $this->assertSame(1, $result->count());
    }

    #[Test]
    public function nullBindingsWorks(): void
    {
        $table = 'test_table';
        $data = [
            'field' => null,
        ];
        Schema::create($table, static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('field')
                ->nullable();
        });
        DB::table($table)->insert($data);
        $result = DB::table($table)->whereNull('field')->get();
        $this->assertSame(1, $result->count());
    }

    /**
     * @param mixed $value
     */
    #[DataProvider('provideDateTimeBindingsWorksCases')]
    #[Test]
    public function dateTimeBindingsWorks($value): void
    {
        $table = 'test_table';
        $data = [
            'field' => $value,
        ];
        Schema::create($table, static function (Blueprint $table): void {
            $table->increments('id');
            $table->dateTime('field');
        });
        DB::table($table)->insert($data);
        $result = DB::table($table)->select($data);
        $this->assertSame(1, $result->count());
    }

    public static function provideBoolTrueBindingsWorksCases(): iterable
    {
        yield 'true' => [true];

        yield 'false' => [false];
    }

    public static function provideIntBindingsWorksCases(): iterable
    {
        yield 'zero' => [0];

        yield 'non-zero' => [10];
    }

    public static function provideDateTimeBindingsWorksCases(): iterable
    {
        yield 'as string' => ['2019-01-01 13:12:22'];

        yield 'as Carbon object' => [new Carbon('2019-01-01 13:12:22')];
    }
}
