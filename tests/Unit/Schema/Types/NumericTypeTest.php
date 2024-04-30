<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Unit\Schema\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Fuwasegu\Postgres\Schema\Types\NumericType;
use Fuwasegu\Postgres\Tests\TestCase;
use Override;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 *
 * @coversNothing
 */
final class NumericTypeTest extends TestCase
{
    /**
     * @var AbstractPlatform
     */
    private $abstractPlatform;

    /**
     * @var NumericType
     */
    private $type;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->type = $this
            ->getMockBuilder(NumericType::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->abstractPlatform = $this->getMockForAbstractClass(AbstractPlatform::class);
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
