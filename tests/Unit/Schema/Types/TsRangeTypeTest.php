<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Tests\Unit\Schema\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Fuwasegu\Postgres\Schema\Types\TsRangeType;
use Fuwasegu\Postgres\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;

final class TsRangeTypeTest extends TestCase
{
    private AbstractPlatform&MockInterface $abstractPlatform;

    private TsRangeType $type;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->type = new TsRangeType();
        $this->abstractPlatform = Mockery::mock(AbstractPlatform::class);
    }

    #[Test]
    public function getSQLDeclaration(): void
    {
        $this->assertSame(TsRangeType::TYPE_NAME, $this->type->getSQLDeclaration([], $this->abstractPlatform));
    }

    #[Test]
    public function getTypeName(): void
    {
        $this->assertSame(TsRangeType::TYPE_NAME, $this->type->getName());
    }
}
