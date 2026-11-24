<?php

namespace App\Policies;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
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
    public function view(User $user, Project $project): bool
    {
        return $project->status === ProjectStatus::Published || $this->canManage($user, $project);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can request adoption of the project.
     */
    public function requestAdoption(User $user, Project $project): bool
    {
        return $project->user_id !== $user->id
            && $project->status === ProjectStatus::Published
            && $project->adoption_available;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        return $this->canManage($user, $project);
    }

    /**
     * Determine whether the user can publish the project.
     */
    public function publish(User $user, Project $project): bool
    {
        return $this->canManage($user, $project);
    }

    /**
     * Determine whether the user can archive the project.
     */
    public function archive(User $user, Project $project): bool
    {
        return $this->canManage($user, $project);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $this->canManage($user, $project)
            && ! $project->revivedProjects()->exists()
            && ! $project->originatingAdoptionRequest()->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }

    private function canManage(User $user, Project $project): bool
    {
        return $user->is_admin || $project->user_id === $user->id;
    }
}
