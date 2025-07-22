<?php

namespace App\Enums\Environment;

enum VariableType: string
{
    case STRING = 'string';
    case SECRET = 'secret';
    case NUMBER = 'number';
    case BOOLEAN = 'boolean';
}
