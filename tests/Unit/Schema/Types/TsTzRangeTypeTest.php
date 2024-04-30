<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Unit\Schema\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Fuwasegu\Postgres\Schema\Types\TsTzRangeType;
use Fuwasegu\Postgres\Tests\TestCase;

class TsTzRangeTypeTest extends TestCase
{
    /**
     * @var AbstractPlatform
     */
    private $abstractPlatform;

    /**
     * @var TsRangeType
     */
    private $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = $this
            ->getMockBuilder(TsTzRangeType::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->abstractPlatform = $this->getMockForAbstractClass(AbstractPlatform::class);
    }

    /**
     * @test
     */
    public function getSQLDeclaration(): void
    {
        $this->assertSame(TsTzRangeType::TYPE_NAME, $this->type->getSQLDeclaration([], $this->abstractPlatform));
    }

    /**
     * @test
     */
    public function getTypeName(): void
    {
        $this->assertSame(TsTzRangeType::TYPE_NAME, $this->type->getName());
    }
}
