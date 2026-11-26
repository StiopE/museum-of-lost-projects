<?php

namespace App\Models;

use App\Enums\AdoptionRequestStatus;
use Database\Factories\AdoptionRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'user_id', 'message', 'status', 'revived_project_id', 'responded_at'])]
class AdoptionRequest extends Model
{
    /** @use HasFactory<AdoptionRequestFactory> */
    use HasFactory;

    /**
     * Get the project being considered for adoption.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who submitted the request.
     *
     * @return BelongsTo<User, $this>
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the project created after this request was approved.
     *
     * @return BelongsTo<Project, $this>
     */
    public function revivedProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'revived_project_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array{status: AdoptionRequestStatus::class, responded_at: 'datetime'}
     */
    protected function casts(): array
    {
        return [
            'status' => AdoptionRequestStatus::class,
            'responded_at' => 'datetime',
        ];
    }
}
