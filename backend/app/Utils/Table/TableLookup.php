<?php

namespace App\Utils\Table;
/**
 * This class is used for declaration and implementation of some methods used to generate
 * the data structure that is used to manipulate data based on the user interactions.
 * Data Filtering, Ordering, Search and Pagination.
 */
class TableLookup
{
    private array|null $filters;
    private array|null $orders;
    private array|null $search_fields;
    private string|null $search_keyword;
    private array|null $count_columns;
    private TablePagination $pagination;

    /**
     * @param array|null $filters
     * @param array|null $orders
     * @param array|null $search_fields
     * @param string|null $search_keyword
     * @param array|null $count_columns
     * @param TablePagination $pagination
     */
    public function __construct(
        ?array $filters,
        ?array $orders,
        ?array $search_fields,
        ?string $search_keyword,
        ?array $count_columns,
        TablePagination $pagination,
    ){
        $this->filters = $filters;
        $this->pagination = $pagination;
        $this->search_keyword = $search_keyword;
        $this->search_fields = $search_fields;
        $this->count_columns = $count_columns;
        $this->orders = $orders;
    }


    public function getFilters(array $default = []): ?array
    {
        return $this->filters ?: $default;
    }

    public function setFilters(?array $filters): void
    {
        $this->filters = $filters;
    }

    public function getPagination(): TablePagination
    {
        return $this->pagination;
    }

    public function setPagination(TablePagination $pagination): void
    {
        $this->pagination = $pagination;
    }

    public function getSearchKeyword(string $default = ''): ?string
    {
        return $this->search_keyword ?: $default;
    }

    public function setSearchKeyword(?string $search_keyword): void
    {
        $this->search_keyword = $search_keyword;
    }

    public function getSearchFields(array $default = []): ?array
    {
        return $this->search_fields ?: $default;
    }

    public function setSearchFields(?array $search_fields): void
    {
        $this->search_fields = $search_fields;
    }

    public function getOrders(array $default = [['field' => 'created_at', 'sort' => 'asc']]): ?array
    {
        return $this->orders ?: $default;
    }

    public function setOrders(?array $orders): void
    {
        $this->orders = $orders;
    }

    public function getCountColumns(): ?array
    {
        return $this->count_columns;
    }

    public function setCountColumns(?array $count_columns): void
    {
        $this->count_columns = $count_columns;
    }
}
