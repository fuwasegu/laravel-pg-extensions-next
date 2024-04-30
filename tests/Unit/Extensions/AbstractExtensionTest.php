<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Tests\Unit\Extensions;

use Fuwasegu\Postgres\Extensions\AbstractComponent;
use Fuwasegu\Postgres\Extensions\AbstractExtension;
use Fuwasegu\Postgres\Extensions\Exceptions\MacroableMissedException;
use Fuwasegu\Postgres\Extensions\Exceptions\MixinInvalidException;
use Fuwasegu\Postgres\Schema\Blueprint;
use Fuwasegu\Postgres\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Override;
use PHPUnit\Framework\Attributes\Test;

final class AbstractExtensionTest extends TestCase
{
    /**
     * @throws MacroableMissedException
     */
    #[Test]
    public function registerInvalidExtension(): void
    {
        $abstractExtension = new ExtensionStub();

        $this->expectException(MixinInvalidException::class);

        // @var AbstractExtension $abstractExtension
        $abstractExtension::register();
    }

    /**
     * @throws MixinInvalidException
     */
    #[Test]
    public function registerWithInvalidMixin(): void
    {
        $abstractExtension = new InvalidExtensionStub();

        $this->expectException(MacroableMissedException::class);

        // @var AbstractExtension $abstractExtension
        $abstractExtension::register();
    }
}

class InvalidExtensionStub extends AbstractExtension
{
    #[Override]
    public static function getName(): string
    {
        return 'extension';
    }

    #[Override]
    public static function getMixins(): array
    {
        return [
            ComponentStub::class => ServiceProvider::class,
        ];
    }
}

class ComponentStub extends AbstractComponent
{
}

class ExtensionStub extends AbstractExtension
{
    #[Override]
    public static function getName(): string
    {
        return 'extension';
    }

    #[Override]
    public static function getMixins(): array
    {
        return [
            InvalidComponentStub::class => Blueprint::class,
        ];
    }
}

class InvalidComponentStub extends Model
{
}
