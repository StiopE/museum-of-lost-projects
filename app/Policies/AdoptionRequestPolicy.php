<?php

namespace App\Policies;

use App\Models\AdoptionRequest;
use App\Models\User;

class AdoptionRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AdoptionRequest $adoptionRequest): bool
    {
        return $user->is_admin
            || $adoptionRequest->user_id === $user->id
            || $adoptionRequest->project->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AdoptionRequest $adoptionRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the original creator can approve the request.
     */
    public function approve(User $user, AdoptionRequest $adoptionRequest): bool
    {
        return $adoptionRequest->project->user_id === $user->id
            || ($user->is_admin && $adoptionRequest->user_id !== $user->id);
    }

    /**
     * Determine whether the original creator can reject the request.
     */
    public function reject(User $user, AdoptionRequest $adoptionRequest): bool
    {
        return $this->approve($user, $adoptionRequest);
    }

    /**
     * Determine whether the requester can cancel the request.
     */
    public function cancel(User $user, AdoptionRequest $adoptionRequest): bool
    {
        return $adoptionRequest->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AdoptionRequest $adoptionRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AdoptionRequest $adoptionRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AdoptionRequest $adoptionRequest): bool
    {
        return false;
    }
}
