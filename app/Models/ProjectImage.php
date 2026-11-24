<?php

namespace App\Models;

use Database\Factories\ProjectImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'disk', 'image_path', 'caption', 'sort_order'])]
class ProjectImage extends Model
{
    /** @use HasFactory<ProjectImageFactory> */
    use HasFactory;

    /**
     * Get the project this image belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
