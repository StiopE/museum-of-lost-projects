<?php

namespace App\Services;

use App\Enums\AdoptionRequestStatus;
use App\Enums\ProjectStatus;
use App\Models\AdoptionRequest;
use App\Models\Project;
use App\Models\User;
use App\Notifications\AdoptionRequestReceived;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdoptionRequestService
{
    /**
     * Submit an adoption request for a project.
     */
    public function submit(Project $project, User $requester, string $message): AdoptionRequest
    {
        return DB::transaction(function () use ($project, $requester, $message): AdoptionRequest {
            $project = Project::query()
                ->whereKey($project->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureProjectIsAvailable($project, $requester);

            $hasPendingRequest = $project->adoptionRequests()
                ->where('user_id', $requester->getKey())
                ->where('status', AdoptionRequestStatus::Pending)
                ->exists();

            if ($hasPendingRequest) {
                throw ValidationException::withMessages([
                    'message' => 'You already have a pending request for this project.',
                ]);
            }

            $request = $project->adoptionRequests()->create([
                'user_id' => $requester->getKey(),
                'message' => $message,
                'status' => AdoptionRequestStatus::Pending,
            ]);

            $project->creator->notify(new AdoptionRequestReceived($project->title, $project->id, $request->id));

            return $request;
        }, attempts: 3);
    }

    /**
     * Approve an adoption request and create its revived project.
     */
    public function approve(AdoptionRequest $adoptionRequest): Project
    {
        return DB::transaction(function () use ($adoptionRequest): Project {
            $project = Project::query()
                ->whereKey($adoptionRequest->project_id)
                ->lockForUpdate()
                ->firstOrFail();

            $adoptionRequest = AdoptionRequest::query()
                ->whereKey($adoptionRequest->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureRequestIsPending($adoptionRequest);
            $this->ensureProjectIsAvailable($project, $adoptionRequest->requester);

            $revivedProject = $project->revivedProjects()->create([
                'user_id' => $adoptionRequest->user_id,
                'category_id' => $project->category_id,
                'title' => $project->title,
                'description' => $project->description,
                'story' => $project->story,
                'abandonment_reason' => $project->abandonment_reason,
                'original_goal' => $project->original_goal,
                'what_went_wrong' => $project->what_went_wrong,
                'what_could_be_improved' => $project->what_could_be_improved,
                'status' => ProjectStatus::Draft,
                'adoption_available' => false,
            ]);

            $adoptionRequest->update([
                'status' => AdoptionRequestStatus::Approved,
                'revived_project_id' => $revivedProject->getKey(),
                'responded_at' => now(),
            ]);

            $project->update(['adoption_available' => false]);

            $project->adoptionRequests()
                ->where('id', '!=', $adoptionRequest->getKey())
                ->where('status', AdoptionRequestStatus::Pending)
                ->update([
                    'status' => AdoptionRequestStatus::Rejected,
                    'responded_at' => now(),
                ]);

            return $revivedProject;
        }, attempts: 3);
    }

    /**
     * Reject an adoption request.
     */
    public function reject(AdoptionRequest $adoptionRequest): void
    {
        $this->resolve($adoptionRequest, AdoptionRequestStatus::Rejected);
    }

    /**
     * Cancel an adoption request submitted by the current requester.
     */
    public function cancel(AdoptionRequest $adoptionRequest): void
    {
        $this->resolve($adoptionRequest, AdoptionRequestStatus::Cancelled);
    }

    /**
     * Mark a pending request as rejected or cancelled.
     */
    private function resolve(AdoptionRequest $adoptionRequest, AdoptionRequestStatus $status): void
    {
        DB::transaction(function () use ($adoptionRequest, $status): void {
            Project::query()
                ->whereKey($adoptionRequest->project_id)
                ->lockForUpdate()
                ->firstOrFail();

            $adoptionRequest = AdoptionRequest::query()
                ->whereKey($adoptionRequest->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureRequestIsPending($adoptionRequest);

            $adoptionRequest->update([
                'status' => $status,
                'responded_at' => now(),
            ]);
        }, attempts: 3);
    }

    /**
     * Ensure the source project is published and accepting adoption requests.
     */
    private function ensureProjectIsAvailable(Project $project, User $requester): void
    {
        if ($project->user_id === $requester->getKey()) {
            throw ValidationException::withMessages([
                'project' => 'You cannot request adoption of your own project.',
            ]);
        }

        if ($project->status !== ProjectStatus::Published || ! $project->adoption_available) {
            throw ValidationException::withMessages([
                'project' => 'This project is not currently available for adoption.',
            ]);
        }
    }

    /**
     * Ensure the request has not already been resolved.
     */
    private function ensureRequestIsPending(AdoptionRequest $adoptionRequest): void
    {
        if ($adoptionRequest->status !== AdoptionRequestStatus::Pending) {
            throw ValidationException::withMessages([
                'adoption_request' => 'This adoption request is no longer pending.',
            ]);
        }
    }
}
