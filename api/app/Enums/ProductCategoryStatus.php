<?php

namespace App\Enums;

enum ProductCategoryStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
}
