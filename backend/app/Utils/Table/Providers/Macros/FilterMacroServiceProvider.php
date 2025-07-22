<?php

namespace App\Utils\Table\Providers\Macros;

use App\Utils\Table\Enum\FilterOperator;
use App\Utils\Table\Enum\FilterType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class FilterMacroServiceProvider extends ServiceProvider
{
    /**
     * Implementation of data filtering for the Table Component
     * @return void
     */
    public function boot(): void
    {
        Builder::macro('filter', function (array $filters, array|null $customColumns = []) {
            try {
                $this->where(function ($query) use ($filters, $customColumns) {
                    FilterMacroServiceProvider::applyFilters(
                        $query,
                        $filters ?? [],
                        $filters['operator'] ?? FilterOperator::AND->value,
                        $customColumns ?: []
                    );
                });
            } catch (\Exception $exception) {
                //
            }

            return $this;
        });
    }

    /**
     * This method formats the query $builder instance based on filters that are applied to the base query $builder
     * @param Builder $builder
     * @param array $filters
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function applyFilters(Builder $builder, array $filters, int $operator, array $customColumns): void
    {
        foreach ($filters as $filter) {
            $filter = (array)$filter;
            if (isset($filter['filters'])) {
                if(isset($filter['not_in_relationship'])){
                    if (count($filter['filters'])) {
                        $builder->whereDoesntHave($filter['not_in_relationship'], function ($query) use ($filter, $customColumns) {
                            FilterMacroServiceProvider::applyFilters(
                                $query, (array)$filter['filters'], $filter['operator'], $customColumns);
                        });
                    }
                } elseif (isset($filter['relationship'])) {
                    if (count($filter['filters'])) {
                        $builder->whereHas($filter['relationship'], function ($query) use ($filter, $customColumns) {
                            FilterMacroServiceProvider::applyFilters(
                                $query, (array)$filter['filters'], $filter['operator'], $customColumns);
                        });
                    }
                } else {
                    $builder->where(function ($query) use ($filter, $customColumns) {
                        FilterMacroServiceProvider::applyFilters(
                            $query, $filter['filters'], $filter['operator'], $customColumns);
                    });
                }
            } else {
                $filter = (array)$filter;
                FilterMacroServiceProvider::applyFilter($builder, $filter, $operator, $customColumns);
            }
        }
    }

    /**
     * Applying filters based on Filter Type
     * @param Builder $builder
     * @param array $filter
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function applyFilter(Builder $builder, array $filter, int $operator, array $customColumns): void
    {
        try {
            if (
                !(isset($filter['value']) || (!$filter['value'] && $filter['value'] != 0))
                || (is_array($filter['value']) && !count($filter['value']))
            ) {
                return;
            }
            switch ((int)$filter['type']) {
                case FilterType::EQUAL->value:
                    FilterMacroServiceProvider::equal(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::NOT_EQUAL->value:
                    FilterMacroServiceProvider::notEqual(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::GREATER_THAN_EQUAL->value:
                    FilterMacroServiceProvider::greaterThanEqual(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::GREATER_THAN->value:
                    FilterMacroServiceProvider::greaterThan(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::LOWER_THAN_EQUAL->value:
                    FilterMacroServiceProvider::lowerThanEqual(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::LOWER_THAN->value:
                    FilterMacroServiceProvider::lowerThan(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::BETWEEN->value:
                    FilterMacroServiceProvider::between(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::IN->value:
                    FilterMacroServiceProvider::in(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::LIKE->value:
                    FilterMacroServiceProvider::like(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::STARTS_WITH->value:
                    FilterMacroServiceProvider::startsWith(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::ENDS_WITH->value:
                    FilterMacroServiceProvider::endsWith(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::JSON_CONTAINS->value:
                    FilterMacroServiceProvider::jsonContains(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::JSON_CONTAINS_ANY->value:
                    FilterMacroServiceProvider::jsonContainsAny(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::JSON_CONTAINS_ALL->value:
                    FilterMacroServiceProvider::jsonContainsAll(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::YEAR_CALC_GT->value:
                    FilterMacroServiceProvider::yearCalculateGT(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::YEAR_CALC_GTE->value:
                    FilterMacroServiceProvider::yearCalculateGTE(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::YEAR_CALC_EQUAL->value:
                    FilterMacroServiceProvider::yearCalculateEqual(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::YEAR_CALC_LT->value:
                    FilterMacroServiceProvider::yearCalculateLT(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::YEAR_CALC_LTE->value:
                    FilterMacroServiceProvider::yearCalculateLTE(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::IS_NULL->value:
                    FilterMacroServiceProvider::isNull(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::IS_NOT_NULL->value:
                    FilterMacroServiceProvider::isNotNull(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::NOT_IN->value:
                    FilterMacroServiceProvider::notIn(
                        $builder, $filter['field'], $filter['value'], $operator, $customColumns);
                    break;
                case FilterType::RELATED_OR_NOT_WITHOUT_ANY_CONDITIONS->value:
                    break;
            }
        } catch (\Exception $exception) {
            return;
        }
    }

    /**
     * Method to retrieve the field where we are applying the filtering
     * @param Builder $builder
     * @param array $customColumns
     * @param string $field
     * @return Expression|string
     */
    public static function getField(Builder $builder, array $customColumns, string $field): Expression|string
    {
        $table = $builder->getModel()->getTable();
        $rawField = $customColumns[$table][$field] ?? null;
        if ($rawField) {
            $field = DB::raw($rawField);
        }
        return $field;
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param string|int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function equal(
        Builder $builder, string $field, string|int $value, int $operator, array $customColumns): void
    {
        if ($operator === FilterOperator::AND->value) {
            $builder->where(FilterMacroServiceProvider::getField($builder, $customColumns, $field), $value);
        } else {
            $builder->orWhere(FilterMacroServiceProvider::getField($builder, $customColumns, $field), $value);
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param string|int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function notEqual(
        Builder $builder, string $field, string|int $value, int $operator, array $customColumns): void
    {
        if ($operator === FilterOperator::AND->value) {
            $builder->where(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '!=', $value);
        } else {
            $builder->orWhere(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '!=', $value);
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param string|int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function greaterThanEqual(
        Builder $builder, string $field, string|int $value, int $operator, array $customColumns): void
    {
        if ($operator === FilterOperator::AND->value) {
            $builder->where(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '>=', $value);
        } else {
            $builder->orWhere(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '>=', $value);
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param string|int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function greaterThan(
        Builder $builder, string $field, string|int $value, int $operator, array $customColumns): void
    {
        if ($operator === FilterOperator::AND->value) {
            $builder->where(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '>', $value);
        } else {
            $builder->orWhere(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '>', $value);
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param string|int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function lowerThanEqual(
        Builder $builder, string $field, string|int $value, int $operator, array $customColumns): void
    {
        if ($operator === FilterOperator::AND->value) {
            $builder->where(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '<=', $value);
        } else {
            $builder->orWhere(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '<=', $value);
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param string|int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function lowerThan(
        Builder $builder, string $field, string|int $value, int $operator, array $customColumns): void
    {
        if ($operator === FilterOperator::AND->value) {
            $builder->where(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '<', $value);
        } else {
            $builder->orWhere(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '<', $value);
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param array $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function between(
        Builder $builder, string $field, array $value, int $operator, array $customColumns): void
    {
        if ($operator === FilterOperator::AND->value) {
            $builder->whereBetween(FilterMacroServiceProvider::getField($builder, $customColumns, $field), $value);
        } else {
            $builder->orWhereBetween(FilterMacroServiceProvider::getField($builder, $customColumns, $field), $value);
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param array $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function in(
        Builder $builder, string $field, array $value, int $operator, array $customColumns): void
    {
        if ($operator === FilterOperator::AND->value) {
            $builder->whereIn(FilterMacroServiceProvider::getField($builder, $customColumns, $field), $value);
        } else {
            $builder->orWhereIn(FilterMacroServiceProvider::getField($builder, $customColumns, $field), $value);
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param string|int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function like(
        Builder $builder, string $field, string|int $value, int $operator, array $customColumns): void
    {
        if ($operator === FilterOperator::AND->value) {
            $builder->where(FilterMacroServiceProvider::getField($builder, $customColumns, $field), 'ILIKE', '%' . $value . '%');
        } else {
            $builder->orWhere(FilterMacroServiceProvider::getField($builder, $customColumns, $field), 'ILIKE', '%' . $value . '%');
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param string|int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function startsWith(
        Builder $builder, string $field, string|int $value, int $operator, array $customColumns): void
    {
        if ($operator === FilterOperator::AND->value) {
            $builder->where(FilterMacroServiceProvider::getField($builder, $customColumns, $field), 'ILIKE',$value . '%');
        } else {
            $builder->orWhere(FilterMacroServiceProvider::getField($builder, $customColumns, $field), 'ILIKE',$value . '%');
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param string|int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function endsWith(
        Builder $builder, string $field, string|int $value, int $operator, array $customColumns): void
    {
        if ($operator === FilterOperator::AND->value) {
            $builder->where(FilterMacroServiceProvider::getField($builder, $customColumns, $field), 'ILIKE',$value . '%');
        } else {
            $builder->orWhere(FilterMacroServiceProvider::getField($builder, $customColumns, $field), 'ILIKE',$value . '%');
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param string|int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function jsonContains(
        Builder $builder, string $field, string|int $value, int $operator, array $customColumns): void
    {
        if ($operator === FilterOperator::AND->value) {
            $builder->whereJsonContains(FilterMacroServiceProvider::getField($builder, $customColumns, $field), $value);
        } else {
            $builder->orWhereJsonContains(FilterMacroServiceProvider::getField($builder, $customColumns, $field), $value);
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param array $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function jsonContainsAny(
        Builder $builder, string $field, array $value, int $operator, array $customColumns): void
    {
        if ($operator === FilterOperator::AND->value) {
            $builder->where(function ($query) use ($field, $value, $builder, $customColumns) {
                foreach ($value as $item) {
                    $query->orWhereJsonContains(FilterMacroServiceProvider::getField($builder, $customColumns, $field), $item);
                }
            });
        } else {
            $builder->orWhere(function ($query) use ($field, $value, $builder, $customColumns) {
                foreach ($value as $item) {
                    $query->orWhereJsonContains(FilterMacroServiceProvider::getField($builder, $customColumns, $field), $item);
                }
            });
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param array $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function jsonContainsAll(
        Builder $builder, string $field, array $value, int $operator, array $customColumns): void
    {
        if ($operator === FilterOperator::AND->value) {
            $builder->where(function ($query) use ($field, $value, $builder, $customColumns) {
                foreach ($value as $item) {
                    $query->whereJsonContains(FilterMacroServiceProvider::getField($builder, $customColumns, $field), $item);
                }
            });
        } else {
            $builder->orWhere(function ($query) use ($field, $value, $builder, $customColumns) {
                foreach ($value as $item) {
                    $query->whereJsonContains(FilterMacroServiceProvider::getField($builder, $customColumns, $field), $item);
                }
            });
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function yearCalculateGT(
        Builder $builder, string $field, int $value, int $operator, array $customColumns): void
    {
        $datetime = now()->subYears($value)->format('Y-m-d 00:00:00');
        if ($operator === FilterOperator::AND->value) {
            $builder->where(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '<', $datetime);
        } else {
            $builder->orWhere(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '<', $datetime);
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function yearCalculateGTE(
        Builder $builder, string $field, int $value, int $operator, array $customColumns): void
    {
        $datetime = now()->subYears($value)->format('Y-m-d 00:00:00');
        if ($operator === FilterOperator::AND->value) {
            $builder->where(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '<=', $datetime);
        } else {
            $builder->orWhere(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '<=', $datetime);
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function yearCalculateEqual(
        Builder $builder, string $field, int $value, int $operator, array $customColumns): void
    {
        $datetime = now()->subYears($value);
        if ($operator === FilterOperator::AND->value) {
            $builder->whereBetween(FilterMacroServiceProvider::getField($builder, $customColumns, $field), [
                $datetime->format('Y-m-d 00:00:00'),
                $datetime->format('Y-m-d 23:59:59')
            ]);
        } else {
            $builder->orWhereBetween(FilterMacroServiceProvider::getField($builder, $customColumns, $field), [
                $datetime->format('Y-m-d 00:00:00'),
                $datetime->format('Y-m-d 23:59:59')
            ]);
        }

    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function yearCalculateLT(
        Builder $builder, string $field, int $value, int $operator, array $customColumns): void
    {
        $datetime = now()->subYears($value)->format('Y-m-d 00:00:00');
        if ($operator === FilterOperator::AND->value) {
            $builder->where(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '>', $datetime);
        } else {
            $builder->orWhere(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '>', $datetime);
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function yearCalculateLTE(
        Builder $builder, string $field, int $value, int $operator, array $customColumns): void
    {
        $datetime = now()->subYears($value)->format('Y-m-d 00:00:00');
        if ($operator === FilterOperator::AND->value) {
            $builder->where(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '>=', $datetime);
        } else {
            $builder->orWhere(FilterMacroServiceProvider::getField($builder, $customColumns, $field), '>=', $datetime);
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function isNull(
        Builder $builder, string $field, int $value, int $operator, array $customColumns): void
    {
        if ($value) {
            if ($operator == FilterOperator::AND->value) {
                $builder->whereNull(FilterMacroServiceProvider::getField($builder, $customColumns, $field));
            } else {
                $builder->orWhereNull(FilterMacroServiceProvider::getField($builder, $customColumns, $field));
            }
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param int $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function isNotNull(
        Builder $builder, string $field, int $value, int $operator, array $customColumns): void
    {
        if ($value) {
            if ($operator == FilterOperator::AND->value) {
                $builder->whereNotNull(FilterMacroServiceProvider::getField($builder, $customColumns, $field));
            } else {
                $builder->orWhereNotNull(FilterMacroServiceProvider::getField($builder, $customColumns, $field));
            }
        }
    }

    /**
     * @param Builder $builder
     * @param string $field
     * @param array $value
     * @param int $operator
     * @param array $customColumns
     * @return void
     */
    public static function notIn(
        Builder $builder, string $field, array $value, int $operator, array $customColumns): void
    {
        if ($operator === FilterOperator::AND->value) {
            $builder->whereNotIn(FilterMacroServiceProvider::getField($builder, $customColumns, $field), $value);
        } else {
            $builder->orWhereNotIn(FilterMacroServiceProvider::getField($builder, $customColumns, $field), $value);
        }
    }
}
