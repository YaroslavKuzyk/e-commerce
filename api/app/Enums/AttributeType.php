<?php

namespace App\Enums;

enum AttributeType: string
{
    case SELECT = 'select';
    case MULTI_SELECT = 'multi_select';
    case CHECKBOX = 'checkbox';
    case SWITCH = 'switch';
    case COLOR = 'color';
}
