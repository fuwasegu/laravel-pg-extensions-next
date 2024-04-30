<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Unit\Schema\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Fuwasegu\Postgres\Schema\Types\TsRangeType;
use Fuwasegu\Postgres\Tests\TestCase;
use Override;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 *
 * @coversNothing
 */
final class TsRangeTypeTest extends TestCase
{
    /**
     * @var AbstractPlatform
     */
    private $abstractPlatform;

    /**
     * @var TsRangeType
     */
    private $type;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->type = $this
            ->getMockBuilder(TsRangeType::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->abstractPlatform = $this->getMockForAbstractClass(AbstractPlatform::class);
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
