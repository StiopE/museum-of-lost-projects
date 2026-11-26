<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Published = 'published';
    case Archived = 'archived';
}
