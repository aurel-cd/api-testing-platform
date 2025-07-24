<?php

namespace App\Utils\Table\Providers\Macros;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;

class SearchMacroServiceProvider extends ServiceProvider
{
    /**
     * Implementation of search functionality for the Table component
     * @return void
     */
    public function boot(): void
    {
        Builder::macro('search', function (array $fields = [], string $keyword = null, array $customColumns = []) {
            if (!$keyword) {
                return $this;
            }

            $newFields = [
                'normal' => [],
                'relationship' => []
            ];
            foreach ($fields as $field) {
                $relationshipFields = explode('.', $field);
                if (count($relationshipFields) == 1) {
                    $newFields['normal'][] = $relationshipFields[0];
                } else {
                    $relationshipField = array_pop($relationshipFields);
                    $relationship = implode('.', $relationshipFields);
                    if (!isset($newFields['relationship'][$relationship])) {
                        $newFields['relationship'][$relationship] = [];
                    }
                    $newFields['relationship'][$relationship][] = $relationshipField;
                }
            }

            $this->where(function (Builder $builder) use ( $newFields, $keyword, $customColumns) {
                if (count($newFields['normal'])) {
                    $normalFields = [];
                    $table = $builder->getModel()->getTable();
                    $customFieldsMapping = [];
                    foreach ($newFields['normal'] as $normalField) {
                        if (isset($customColumns[$table][$normalField])) {
                            $customFieldsMapping[] = $table . ':' . $normalField;
                        } else {
                            $normalFields[] = $normalField;
                        }
                    }

                    $rawSearch = SearchMacroServiceProvider::searchQuery(
                        implode(' ', array_merge($normalFields, $customFieldsMapping)), $keyword);

                    foreach (array_merge($normalFields, $customFieldsMapping) as $field) {
                        $rawSearch = str_replace($field, "COALESCE({$field}, '')", $rawSearch);
                    }

                    foreach ($customFieldsMapping as $customFieldMap) {
                        $customFieldMapEx = explode(':', $customFieldMap);
                        $rawExpression = $customColumns[$customFieldMapEx[0]][$customFieldMapEx[1]];
                        $rawSearch = str_replace($customFieldMap, $rawExpression, $rawSearch);
                    }

                    $builder->whereRaw($rawSearch);
                }

                foreach ($newFields['relationship'] as $relationshipKey => $fields) {
                    $builder->orWhere(function (Builder $builder) use ($fields, $keyword, $relationshipKey, $customColumns) {
                        SearchMacroServiceProvider::relationshipSearchFn($builder, $relationshipKey, $fields, $keyword, $customColumns);
                    });
                }
            });

            return $this;
        });
    }

    public static function depthPicker($arr, $temp_string, &$collect): void
    {
        if ($temp_string != "")
            $collect []= $temp_string;

        for ($i=0, $iMax = sizeof($arr); $i < $iMax; $i++) {
            $arrcopy = $arr;
            $elem = array_splice($arrcopy, $i, 1); // removes and returns the i'th element
            if (sizeof($arrcopy) > 0) {
                SearchMacroServiceProvider::depthPicker($arrcopy, $temp_string ." " . $elem[0], $collect);
            } else {
                $collect []= $temp_string. " " . $elem[0];
            }
        }
    }

    public static function findCombinations(string $string): array
    {
        $array = explode(' ', $string);
        $size = count($array);
        $collect = [];
        SearchMacroServiceProvider::depthPicker($array, "", $collect);

        $newArr = [];
        foreach ($collect as $item) {
            $arr = explode(' ', trim($item));
            if ($size == count($arr)) {
                $newArr[] = $arr;
            }
        }

        return $newArr;
    }

    public static function searchQuery(string $fields, string $keyword): string {
        $combinations = SearchMacroServiceProvider::findCombinations($fields);
        $queries = [];

        foreach ($combinations as $combination) {
            $queries[] = "CONCAT(" . implode(", ' ', ", $combination) . ") ILIKE '%{$keyword}%'";
        }

        return "(" . implode(" OR ", $queries) . ")";
    }

    public static function relationshipSearchFn (Builder $builder, string $relationshipKeysString, array $searchFields,
                                                 string $keyword, array $customColumns = []): Builder
    {
        if (!$relationshipKeysString) {
            $searchFieldsApplied = [];
            $table = $builder->getModel()->getTable();
            $customFieldsMapping = [];
            foreach ($searchFields as $searchField) {
                if (isset($customColumns[$table][$searchField])) {
                    $customFieldsMapping[] = $table . ':' . $searchField;
                } else {
                    $searchFieldsApplied[] = $searchField;
                }
            }

            $rawSearch = SearchMacroServiceProvider::searchQuery(
                implode(' ', array_merge($searchFieldsApplied, $customFieldsMapping)), $keyword);

            foreach (array_merge($searchFieldsApplied, $customFieldsMapping) as $field) {
                $rawSearch = str_replace($field, "COALESCE({$field}, '')", $rawSearch);
            }

            foreach ($customFieldsMapping as $customFieldMap) {
                $customFieldMapEx = explode(':', $customFieldMap);
                $rawExpression = $customColumns[$customFieldMapEx[0]][$customFieldMapEx[1]];
                $rawSearch = str_replace($customFieldMap, $rawExpression, $rawSearch);
            }

            return $builder->whereRaw($rawSearch);
        } else {
            $relationshipKeys = explode('.', $relationshipKeysString);
            $relationship = $relationshipKeys[0];
            unset($relationshipKeys[0]);
            $relationshipKeys = implode('.', array_values($relationshipKeys));
            return $builder->whereHas(
                $relationship,
                function (Builder $builder) use ($relationshipKeys, $searchFields, $keyword, $customColumns) {
                    SearchMacroServiceProvider::relationshipSearchFn($builder, $relationshipKeys, $searchFields,  $keyword, $customColumns);
                }
            );
        }
    }

}
