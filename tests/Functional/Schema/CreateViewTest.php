<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Tests\Functional\Schema;

use Fuwasegu\Postgres\Helpers\ViewAssertions;
use Fuwasegu\Postgres\Schema\Blueprint;
use Fuwasegu\Postgres\Tests\FunctionalTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Override;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 *
 * @coversNothing
 */
final class CreateViewTest extends FunctionalTestCase
{
    use DatabaseTransactions;

    use ViewAssertions;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_table', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
        });
    }

    #[Override]
    protected function tearDown(): void
    {
        Schema::dropIfExists('test_table');

        parent::tearDown();
    }

    #[Test]
    public function createFacadeView(): void
    {
        Schema::createView('test_view', 'select * from test_table where name is not null');

        $this->seeView('test_view');
        $this->assertSameView(
            'select test_table.id, test_table.name from test_table where (test_table.name is not null);',
            'test_view',
        );

        Schema::dropView('test_view');
        $this->notSeeView('test_view');
    }

    #[Test]
    public function createBlueprintView(): void
    {
        Schema::table('test_table', static function (Blueprint $table): void {
            $table->createView('test_view', 'select * from test_table where name is not null');
        });

        $this->seeView('test_view');
        $this->assertSameView(
            'select test_table.id, test_table.name from test_table where (test_table.name is not null);',
            'test_view',
        );

        Schema::table('users', static function (Blueprint $table): void {
            $table->dropView('test_view');
        });

        $this->notSeeView('test_view');
    }
}
