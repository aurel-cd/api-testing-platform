<?php

namespace App\Utils\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * The Table class is the central component responsible for managing the presentation of tabular data.
 * It handles the data retrieval process by integrating filtering, sorting, searching and pagination functionalities.
 */
class Table
{
    /**
     * @var Builder
     */
    private Builder $builder;

    /**
     * @var array
     */
    private array $filters;

    /**
     * @var array
     */
    private array $orders;

    /**
     * @var array
     */
    private array $searchFields;

    /**
     * @var string|null
     */
    private string|null $searchKeyword;

    /**
     * @var string|null
     */
    private string|null $resourceClass;

    /**
     * @var array|null
     */
    private array|null $countColumns;

    /**
     * @var array|null
     */
    private array|null $customColumns;

    /**
     * @var string|null
     */
    private string|null $tableSelect;

    /**
     * @var Builder|null
     */
    private Builder|null $queryBuilder;

    /**
     * @var bool
     */
    private bool $useDtoSchema;

    /**
     * @param Builder $builder
     * @param array $filters
     * @param array $orders
     * @param array $searchFields
     * @param string|null $searchKeyword
     * @param string|null $resourceClass
     * @param array|null $customColumns
     * @param string|null $tableSelect
     * @param Builder|null $queryBuilder
     */
    public function __construct(
        Builder $builder,
        array $filters = [],
        array $orders = [],
        array $searchFields = [],
        string|null $searchKeyword = null,
        string|null $resourceClass = null,
        array|null $customColumns = [],
        string|null $tableSelect = null,
        Builder|null $queryBuilder = null
    ) {
        $this->builder = $builder;
        $this->filters = $filters;
        $this->orders = $orders;
        $this->searchFields = $searchFields;
        $this->searchKeyword = $searchKeyword;
        $this->resourceClass = $resourceClass;
        $this->customColumns = $customColumns;
        $this->tableSelect = $tableSelect;
        $this->queryBuilder = $queryBuilder;
        $this->useDtoSchema = true;
    }

    public static function basicCreate(Builder $query, string $resourceClass = null, array $request = null): self
    {
        $tableParams = extractTableParams($request);
        $instance = new self(
            $query,
            $tableParams->getFilters(),
            $tableParams->getOrders(),
            $tableParams->getSearchFields(),
            $tableParams->getSearchKeyword(),
            $resourceClass
        );

        $instance->setCountColumns($tableParams->getCountColumns());

        return $instance;
    }

    /**
     * This method formats the query builder to filter the data based on filters, orders and search keywords
     * that are applied by the user
     * @return Builder
     */
    public function getQueryBuilder(): Builder
    {
        if ($this->queryBuilder) {
            return $this->queryBuilder;
        }
        $builder = clone $this->builder;
        $selected = $builder->getQuery()->getColumns();
        if (!$this->tableSelect && (!count($selected) || (count($selected) == 1 && $selected[0] == '*'))) {
            $builder->select($builder->getModel()->getTable() . '.*');
        }
        $builder->filter($this->filters, $this->customColumns);
        if ((count($this->searchFields) || count($this->customColumns))  && $this->searchKeyword) {
            $builder->search($this->searchFields, $this->searchKeyword, $this->customColumns);
        }

        foreach ($this->orders as $order) {
            $by = $order['field'];
            $dir = $order['sort'];
            $by = explode('.', $by);
            if (count($by) == 1) {
                $builder->orderBy($by[0], $dir);
                continue;
            }
            $relationships = $by;
            $by = array_pop($relationships);
            $lastRelationship = null;
            foreach ($relationships as $relationship) {
                /**
                 * @var HasOne $lastRelationship
                 */
                if (!$lastRelationship) {
                    $currentModel = $builder->getModel();
                    $lastRelationship = $currentModel->{$relationship}();
                    $nextModel = $lastRelationship->getModel();
                } else {
                    $currentModel = $lastRelationship->getModel();
                    $lastRelationship = $currentModel->{$relationship}();
                    $nextModel = $lastRelationship->getModel();
                }
                $joinTable = $nextModel->getTable();
                if ($lastRelationship instanceof BelongsTo) {
                    $joinTableKey = $lastRelationship->getOwnerKeyName();
                    $localTableKey = $lastRelationship->getForeignKeyName();
                } else {
                    $joinTableKey = $lastRelationship->getForeignKeyName();
                    $localTableKey = $lastRelationship->getLocalKeyName();
                }
                $localKey = $currentModel->getTable() . '.' . $localTableKey;
                $foreignKey = $joinTable . '.' . $joinTableKey;
                $builder->leftJoin($joinTable, $localKey, '=', $foreignKey);
            }

            $builder->orderBy($joinTable . '.' . $by, $dir);
        }

        $this->queryBuilder = $builder;
        return $this->queryBuilder;
    }

    public function count(): array
    {
        $queryBuilder = $this->getQueryBuilder()->clone();
        $queryBuilder->getQuery()->orders = null;
        $queryBuilder->getQuery()->columns = null;
        $countColumns = is_array($this->countColumns) ? $this->countColumns : [];

        if (!count($countColumns)) {
            return [
                'table__count' => $queryBuilder->selectRaw("COUNT(*) as table__count")->first()->table__count
            ];
        }

        $countColumnsStr = implode(', ', $countColumns);
        $queryBuilder->selectRaw("COUNT(*) as table__count, {$countColumnsStr}")->groupBy($countColumns);
        return $queryBuilder->get()->toArray();
    }

    /**
     * @return mixed
     */
    public function get(): mixed
    {
        $results = $this->getQueryBuilder()->get();

        if (!$this->resourceClass) {
            return $results;
        }

        return $this->useDtoSchema
            ? $results->map(function (Model $model) {
                return call_user_func("{$this->resourceClass}::fromModel", $model)->toArray();
            })
            : call_user_func("{$this->resourceClass}::collection", $results);
    }

    /**
     * This method is used to apply the pagination on the query builder
     * @param int $perPage
     * @param int $page
     * @return array
     */
    public function paginate(int $perPage = 10, int $page = 1): array
    {
        $page = $this->getQueryBuilder()->paginate(perPage: $perPage, page: $page);
        $currentPage = $page->currentPage();
        $totalPages = $page->lastPage();

        if (!$this->resourceClass) {
            $results = $page->items();
        } else {
            $results = $this->useDtoSchema
                ? array_map(
                    fn(Model $model) => call_user_func("{$this->resourceClass}::fromModel", $model)->toArray(),
                    $page->items()
                )
                : call_user_func("{$this->resourceClass}::collection", $page->items());
        }

        return [
            'results' => $results,
            'per_page' => $page->perPage(),
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'total' => $page->total(),
            'has_next' => $currentPage < $totalPages,
            'has_previous' => $currentPage != 1
        ];
    }

    /**
     * @param TablePagination|null $pagination
     * @return array
     */
    public function paginateTable(TablePagination $pagination = null): array
    {
        if (!$pagination) {
            $pagination = extractTableParams()->getPagination();
        }

        return $this->paginate($pagination->getPageSize(), $pagination->getPage());
    }

    /**
     * @param Builder $builder
     */
    public function setBuilder(Builder $builder): void
    {
        $this->builder = $builder;
    }

    /**
     * @param array $filters
     */
    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    /**
     * @param array $orders
     */
    public function setOrders(array $orders): void
    {
        $this->orders = $orders;
    }

    /**
     * @param array $searchFields
     */
    public function setSearchFields(array $searchFields): void
    {
        $this->searchFields = $searchFields;
    }

    /**
     * @param string|null $searchKeyword
     */
    public function setSearchKeyword(?string $searchKeyword): void
    {
        $this->searchKeyword = $searchKeyword;
    }

    /**
     * @param array|null $customColumns
     * @return void
     */
    public function setCustomColumns(array|null $customColumns): void
    {
        $this->customColumns = $customColumns;
    }


    /**
     * @param string|null $tableSelect
     * @return void
     */
    public function setTableSelect(string|null $tableSelect): void
    {
        $this->tableSelect = $tableSelect;
    }

    /**
     * @param string|null $resourceClass
     */
    public function setResourceClass(?string $resourceClass): void
    {
        $this->resourceClass = $resourceClass;
    }


    public function setCountColumns(?array $countColumns): void
    {
        $this->countColumns = $countColumns;
    }

    /**
     * Make results to be serialized using resource classes
     *
     * @return void
     */
    public function useResourceSchema(): void
    {
        $this->useDtoSchema = false;
    }

    /**
     * Make results to be serialized using BaseDto classes
     *
     * @return void
     */
    public function useDtoSchema(): void
    {
        $this->useDtoSchema = true;
    }
}
