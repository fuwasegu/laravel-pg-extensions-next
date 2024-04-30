<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres;

use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Events;
use Fuwasegu\Postgres\Extensions\AbstractExtension;
use Fuwasegu\Postgres\Extensions\Exceptions\ExtensionInvalidException;
use Fuwasegu\Postgres\Schema\Builder;
use Fuwasegu\Postgres\Schema\Grammars\PostgresGrammar;
use Fuwasegu\Postgres\Schema\Subscribers\SchemaAlterTableChangeColumnSubscriber;
use Fuwasegu\Postgres\Schema\Types\NumericType;
use Fuwasegu\Postgres\Schema\Types\TsRangeType;
use Fuwasegu\Postgres\Schema\Types\TsTzRangeType;
use Illuminate\Database\PostgresConnection as BasePostgresConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Traits\Macroable;
use Override;
use PDO;

class PostgresConnection extends BasePostgresConnection
{
    use Macroable;

    public $name;

    private static $extensions = [];

    private $initialTypes = [
        TsRangeType::TYPE_NAME => TsRangeType::class,
        TsTzRangeType::TYPE_NAME => TsTzRangeType::class,
        NumericType::TYPE_NAME => NumericType::class,
    ];

    /**
     * @param AbstractExtension|string $extension
     *
     * @codeCoverageIgnore
     */
    final public static function registerExtension(string $extension): void
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
    public function getSchemaBuilder()
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

    public function getDoctrineConnection(): Connection
    {
        $doctrineConnection = parent::getDoctrineConnection();
        $this->overrideDoctrineBehavior($doctrineConnection);

        return $doctrineConnection;
    }

    #[Override]
    public function bindValues($statement, $bindings): void
    {
        if ($this->getPdo()->getAttribute(PDO::ATTR_EMULATE_PREPARES)) {
            foreach ($bindings as $key => $value) {
                $parameter = \is_string($key) ? $key : $key + 1;

                switch (true) {
                    case \is_bool($value):
                        $dataType = PDO::PARAM_BOOL;

                        break;

                    case $value === null:
                        $dataType = PDO::PARAM_NULL;

                        break;

                    default:
                        $dataType = PDO::PARAM_STR;
                }

                $statement->bindValue($parameter, $value, $dataType);
            }
        } else {
            parent::bindValues($statement, $bindings);
        }
    }

    #[Override]
    public function prepareBindings(array $bindings)
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
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new PostgresGrammar());
    }

    private function registerInitialTypes(): void
    {
        foreach ($this->initialTypes as $type => $typeClass) {
            DB::registerDoctrineType($typeClass, $type, $type);
        }
    }

    /**
     * @codeCoverageIgnore
     */
    private function registerExtensions(): void
    {
        collect(self::$extensions)->each(static function ($extension): void {
            // @var AbstractExtension $extension
            $extension::register();
            foreach ($extension::getTypes() as $type => $typeClass) {
                DB::registerDoctrineType($typeClass, $type, $type);
            }
        });
    }

    private function overrideDoctrineBehavior(Connection $connection): Connection
    {
        $eventManager = $connection->getEventManager();
        if (! $eventManager->hasListeners(Events::onSchemaAlterTableChangeColumn)) {
            $eventManager->addEventSubscriber(new SchemaAlterTableChangeColumnSubscriber());
        }
        $connection
            ->getDatabasePlatform()
            ->setEventManager($eventManager);

        return $connection;
    }
}
