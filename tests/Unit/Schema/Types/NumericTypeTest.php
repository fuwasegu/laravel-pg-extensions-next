<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Tests\Unit\Schema\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Fuwasegu\Postgres\Schema\Types\NumericType;
use Fuwasegu\Postgres\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;

final class NumericTypeTest extends TestCase
{
    private AbstractPlatform&MockInterface $abstractPlatform;

    private NumericType $type;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->type = new NumericType();
        $this->abstractPlatform = Mockery::mock(AbstractPlatform::class);
    }

    #[Test]
    public function getSQLDeclaration(): void
    {
        $this->assertSame(NumericType::TYPE_NAME, $this->type->getSQLDeclaration([], $this->abstractPlatform));
    }

    #[Test]
    public function getTypeName(): void
    {
        $this->assertSame(NumericType::TYPE_NAME, $this->type->getName());
    }
}
