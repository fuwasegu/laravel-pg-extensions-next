<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Extensions;

use Fuwasegu\Postgres\Extensions\Exceptions\MacroableMissedException;
use Fuwasegu\Postgres\Extensions\Exceptions\MixinInvalidException;
use Illuminate\Support\Traits\Macroable;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractExtension extends AbstractComponent
{
    abstract public static function getMixins(): array;

    abstract public static function getName(): string;

    public static function getTypes(): array
    {
        return [];
    }

    final public static function register(): void
    {
        collect(static::getMixins())->each(static function ($extension, $mixin): void {
            if (! is_subclass_of($mixin, AbstractComponent::class)) {
                throw new MixinInvalidException(sprintf(
                    'Mixed class %s is not descendant of %s.',
                    $mixin,
                    AbstractComponent::class,
                ));
            }
            if (! method_exists($extension, 'mixin')) {
                throw new MacroableMissedException(sprintf('Class %s doesn’t use Macroable Trait.', $extension));
            }
            // @var Macroable $extension
            $extension::mixin(new $mixin());
        });
    }
}
