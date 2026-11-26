<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'category_id',
    'parent_project_id',
    'title',
    'description',
    'story',
    'abandonment_reason',
    'original_goal',
    'what_went_wrong',
    'what_could_be_improved',
    'status',
    'adoption_available',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /**
     * Get the creator who submitted the project.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the project's category.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the original project this project revives.
     *
     * @return BelongsTo<self, $this>
     */
    public function parentProject(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_project_id');
    }

    /**
     * Get projects revived from this project.
     *
     * @return HasMany<self, $this>
     */
    public function revivedProjects(): HasMany
    {
        return $this->hasMany(self::class, 'parent_project_id');
    }

    /**
     * Get the images attached to this project.
     *
     * @return HasMany<ProjectImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Get the adoption requests for this project.
     *
     * @return HasMany<AdoptionRequest, $this>
     */
    public function adoptionRequests(): HasMany
    {
        return $this->hasMany(AdoptionRequest::class);
    }

    /**
     * Get the adoption request that created this revived project.
     *
     * @return HasOne<AdoptionRequest, $this>
     */
    public function originatingAdoptionRequest(): HasOne
    {
        return $this->hasOne(AdoptionRequest::class, 'revived_project_id');
    }

    /**
     * Get the progress updates for this project.
     *
     * @return HasMany<ProjectUpdate, $this>
     */
    public function updates(): HasMany
    {
        return $this->hasMany(ProjectUpdate::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array{status: ProjectStatus::class, adoption_available: 'boolean'}
     */
    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'adoption_available' => 'boolean',
        ];
    }
}
