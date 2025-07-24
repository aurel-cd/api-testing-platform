<?php

namespace App\Enums\Collection;

enum Visibility: string
{
    case SHARED = 'shared';
    case PUBLIC = 'public';
    case PRIVATE = 'private';
}
