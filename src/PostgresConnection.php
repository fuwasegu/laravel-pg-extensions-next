<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres;

use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Types\Type;
use Fuwasegu\Postgres\Extensions\AbstractExtension;
use Fuwasegu\Postgres\Extensions\Exceptions\ExtensionInvalidException;
use Fuwasegu\Postgres\Schema\Builder;
use Fuwasegu\Postgres\Schema\Grammars\PostgresGrammar;
use Fuwasegu\Postgres\Schema\Types\NumericType;
use Fuwasegu\Postgres\Schema\Types\TsRangeType;
use Fuwasegu\Postgres\Schema\Types\TsTzRangeType;
use Illuminate\Database\Grammar;
use Illuminate\Database\PostgresConnection as BasePostgresConnection;
use Illuminate\Database\Schema\Grammars\PostgresGrammar as IlluminatePostgresGrammar;
use Illuminate\Support\Traits\Macroable;
use Override;
use PDO;

class PostgresConnection extends BasePostgresConnection
{
    use Macroable;

    private static array $extensions = [];

    public array $doctrineTypes = [];

    public ?Connection $doctrineConnection = null;

    public array $doctrineTypeMappings = [];

    private array $initialTypes = [
        TsRangeType::TYPE_NAME => TsRangeType::class,
        TsTzRangeType::TYPE_NAME => TsTzRangeType::class,
        NumericType::TYPE_NAME => NumericType::class,
    ];

    /**
     * @throws ExtensionInvalidException
     */
    final public static function registerExtension(AbstractExtension|string $extension): void
    {
        if (! is_subclass_of($extension, AbstractExtension::class)) {
            throw new ExtensionInvalidException(sprintf(
                'Class %s must be implemented from %s',
                $extension,
                AbstractExtension::class,
            ));
        }
        self::$extensions[$extension::getName()] = $extension;
    }

    #[Override]
    public function getSchemaBuilder(): Builder
    {
        if ($this->schemaGrammar === null) {
            $this->useDefaultSchemaGrammar();
        }

        return new Builder($this);
    }

    #[Override]
    public function useDefaultPostProcessor(): void
    {
        parent::useDefaultPostProcessor();

        $this->registerExtensions();
        $this->registerInitialTypes();
    }

    #[Override]
    public function bindValues($statement, $bindings): void
    {
        if ($this->getPdo()->getAttribute(PDO::ATTR_EMULATE_PREPARES)) {
            foreach ($bindings as $key => $value) {
                $parameter = \is_string($key) ? $key : $key + 1;

                $dataType = match (true) {
                    \is_bool($value) => PDO::PARAM_BOOL,
                    $value === null => PDO::PARAM_NULL,
                    default => PDO::PARAM_STR,
                };

                $statement->bindValue($parameter, $value, $dataType);
            }
        } else {
            parent::bindValues($statement, $bindings);
        }
    }

    #[Override]
    public function prepareBindings(array $bindings): array
    {
        if ($this->getPdo()->getAttribute(PDO::ATTR_EMULATE_PREPARES)) {
            $grammar = $this->getQueryGrammar();

            foreach ($bindings as $key => $value) {
                if ($value instanceof DateTimeInterface) {
                    $bindings[$key] = $value->format($grammar->getDateFormat());
                }
            }

            return $bindings;
        }

        return parent::prepareBindings($bindings);
    }

    #[Override]
    protected function getDefaultSchemaGrammar(): Grammar|IlluminatePostgresGrammar
    {
        return $this->withTablePrefix(new PostgresGrammar());
    }

    private function registerInitialTypes(): void
    {
        foreach ($this->initialTypes as $type => $typeClass) {
            $this->registerDoctrineType($typeClass, $type, $type);
        }
    }

    public function registerDoctrineType(string $class, string $name, string $type): void
    {
        if (! Type::hasType($name)) {
            try {
                Type::addType($name, $class);
            } catch (Exception) {
            }
        }

        $this->doctrineTypes[$name] = [$type, $class];
    }

    private function registerExtensions(): void
    {
        collect(self::$extensions)->each(function ($extension): void {
            // @var AbstractExtension $extension
            $extension::register();
            foreach ($extension::getTypes() as $type => $typeClass) {
                $this->registerDoctrineType($typeClass, $type, $type);
            }
        });
    }

    /**
     * @throws Exception
     */
    public function getDoctrineConnection(): Connection
    {
        if (!$this->doctrineConnection instanceof Connection) {
            $driver = $this->getDoctrineDriver();

            $this->doctrineConnection = new Connection(array_filter([
                'pdo' => $this->getPdo(),
                'dbname' => $this->getDatabaseName(),
                'driver' => $driver->getName(),
                'serverVersion' => $this->getConfig('server_version'),
            ]), $driver);

            foreach ($this->doctrineTypeMappings as $name => $type) {
                $this->doctrineConnection
                    ->getDatabasePlatform()
                    ->registerDoctrineTypeMapping($type, $name);
            }
        }
        assert($this->doctrineConnection instanceof Connection);

        return $this->doctrineConnection;
    }

    public function getDoctrineDriver(): PostgresDriver
    {
        return new PostgresDriver();
    }

    /**
     * @throws Exception
     */
    public function getDoctrineSchemaManager(): AbstractSchemaManager
    {
        return $this->getDoctrineConnection()->createSchemaManager();
    }
}
