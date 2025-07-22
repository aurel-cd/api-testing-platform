<?php

namespace App\Utils\Table\Enum;

enum FilterType: int
{
    case EQUAL = 1;
    case NOT_EQUAL = 2;
    case GREATER_THAN_EQUAL = 3;
    case GREATER_THAN = 4;
    case LOWER_THAN_EQUAL = 5;
    case LOWER_THAN = 6;
    case BETWEEN = 7;
    case IN = 8;
    case LIKE = 9;
    case STARTS_WITH = 10;
    case ENDS_WITH = 11;
    case JSON_CONTAINS = 12;
    case YEAR_CALC_GT = 13;
    case YEAR_CALC_GTE = 14;
    case YEAR_CALC_EQUAL = 15;
    case YEAR_CALC_LT = 16;
    case YEAR_CALC_LTE = 17;
    case IS_NULL = 18;
    case JSON_CONTAINS_ANY = 19;
    case JSON_CONTAINS_ALL = 20;
    case IS_NOT_NULL = 21;
    case NOT_IN = 22;
    case RELATED_OR_NOT_WITHOUT_ANY_CONDITIONS = 23;
}
