<?php

namespace App\Enums;

enum ProductVariantStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
}
