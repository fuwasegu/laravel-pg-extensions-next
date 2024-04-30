<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Schema\Builders;

use Illuminate\Support\Fluent;

/**
 * @see Fluent
 */
trait WhereBuilderTrait
{
    protected $attributes = [];

    public function whereRaw(string $sql, array $bindings = [], string $boolean = 'and'): self
    {
        return $this->compileWhere('Raw', $boolean, ['sql' => $sql, 'bindings' => $bindings]);
    }

    public function where(string $column, string $operator, string $value, string $boolean = 'and'): self
    {
        return $this->compileWhere('Basic', $boolean, ['column' => $column, 'operator' => $operator, 'value' => $value]);
    }

    public function whereColumn(string $first, string $operator, string $second, string $boolean = 'and'): self
    {
        return $this->compileWhere('Column', $boolean, ['first' => $first, 'operator' => $operator, 'second' => $second]);
    }

    public function whereIn(string $column, array $values, string $boolean = 'and', bool $not = false): self
    {
        return $this->compileWhere($not ? 'NotIn' : 'In', $boolean, ['column' => $column, 'values' => $values]);
    }

    public function whereNotIn(string $column, array $values = [], string $boolean = 'and'): self
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    public function whereNull(string $column, string $boolean = 'and', bool $not = false): self
    {
        return $this->compileWhere($not ? 'NotNull' : 'Null', $boolean, ['column' => $column]);
    }

    public function whereBetween(string $column, array $values = [], string $boolean = 'and', bool $not = false): self
    {
        return $this->compileWhere('Between', $boolean, ['column' => $column, 'values' => $values, 'not' => $not]);
    }

    public function whereNotBetween(string $column, array $values = [], string $boolean = 'and'): self
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    public function whereNotNull(string $column, string $boolean = 'and'): self
    {
        return $this->whereNull($column, $boolean, true);
    }

    protected function compileWhere(string $type, string $boolean, array $parameters = []): self
    {
        $this->attributes['wheres'][] = array_merge(['type' => $type, 'boolean' => $boolean], $parameters);

        return $this;
    }
}
