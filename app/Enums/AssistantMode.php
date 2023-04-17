<?php

namespace App\Enums;

use App\Enums\Traits\Values;

enum AssistantMode: string
{
    use Values;

    case INFORMATIVE = 'informative';
    case CONVERSATIONAL = 'conversational';
}
