<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdoptionRequestRequest;
use App\Models\AdoptionRequest;
use App\Models\Project;
use App\Services\AdoptionRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class AdoptionRequestController extends Controller
{
    public function __construct(private AdoptionRequestService $adoptionRequestService) {}

    public function store(StoreAdoptionRequestRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();

        $this->adoptionRequestService->submit(
            $project,
            $request->user(),
            (string) $validated['message'],
        );

        return redirect()->route('projects.show', $project)->with('status', 'Adoption request sent.');
    }

    public function approve(AdoptionRequest $adoptionRequest): RedirectResponse
    {
        Gate::authorize('approve', $adoptionRequest);

        $this->adoptionRequestService->approve($adoptionRequest);

        return redirect()->route('my.projects.edit', $adoptionRequest->project)->with('status', 'Adoption request approved. A revived project draft was created.');
    }

    public function reject(AdoptionRequest $adoptionRequest): RedirectResponse
    {
        Gate::authorize('reject', $adoptionRequest);

        $this->adoptionRequestService->reject($adoptionRequest);

        return redirect()->route('my.projects.edit', $adoptionRequest->project)->with('status', 'Adoption request rejected.');
    }

    public function cancel(AdoptionRequest $adoptionRequest): RedirectResponse
    {
        Gate::authorize('cancel', $adoptionRequest);

        $this->adoptionRequestService->cancel($adoptionRequest);

        return redirect()->route('projects.show', $adoptionRequest->project)->with('status', 'Adoption request cancelled.');
    }
}
