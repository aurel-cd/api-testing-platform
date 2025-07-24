<?php

if (!function_exists('extractTableParams')) {
    function extractTableParams(array $request = null): object
    {
        $request = $request ?? request()->all();
        if (isset($request['table_operations']) && is_array($request['table_operations'])) {
            $operations = $request['table_operations'];

            if (isset($operations['pagination']) && is_array($operations['pagination'])) {
                $pagination = $operations['pagination'];
                if (isset($pagination['page'])) {
                    $page = $pagination['page'] ?: 0;
                } else {
                    $page = 0;
                }
                if (isset($pagination['pageSize'])) {
                    $pageSize = $pagination['pageSize'] ?: 10;
                } else {
                    $pageSize = 10;
                }
            } else {
                $page = 0;
                $pageSize = 10;
            }
            $page++;

            if (isset($operations['filters']) && is_array($operations['filters'])) {
                $filters = $operations['filters'];
            } else {
                $filters = null;
            }

            if (isset($operations['orders']) && is_array($operations['orders'])) {
                $orders = $operations['orders'];
            } else {
                $orders = null;
            }

            if (isset($operations['search_fields']) && is_array($operations['search_fields'])) {
                $searchFields = $operations['search_fields'];
            } else {
                $searchFields = null;
            }

            if (isset($operations['search_keyword']) && is_string($operations['search_keyword'])) {
                $searchKeyword = $operations['search_keyword'];
            } else {
                $searchKeyword = null;
            }

            if (isset($operations['count_columns']) && is_array($operations['count_columns'])) {
                $countColumns = $operations['count_columns'];
            } else {
                $countColumns = null;
            }

            return new \App\Utils\Table\TableLookup(
                $filters,
                $orders,
                $searchFields,
                $searchKeyword,
                $countColumns,
                new \App\Utils\Table\TablePagination($page, $pageSize),
            );
        } else {
            return new \App\Utils\Table\TableLookup(
                null,
                null,
                null,
                '',
                null,
                new \App\Utils\Table\TablePagination(1, 10),
            );
        }
    }
}
