<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Http\Requests\StoreAdoptionRequestRequest;
use App\Models\AdoptionRequest;
use App\Models\Project;
use App\Services\AdoptionRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdoptionRequestController extends Controller
{
    public function __construct(private AdoptionRequestService $adoptionRequestService) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('my.adoption-requests.index', [
            'received' => $user->receivedAdoptionRequests()->with(['project', 'requester'])
                ->orderByDesc('adoption_requests.id')->paginate(10, ['adoption_requests.*'], 'received_page')->withQueryString(),
            'sent' => $user->adoptionRequests()->with('project')
                ->latest('updated_at')->orderByDesc('id')->paginate(10, ['*'], 'sent_page')->withQueryString(),
        ]);
    }

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

        $redirect = $adoptionRequest->project->status === ProjectStatus::Published
            ? redirect()->route('projects.show', $adoptionRequest->project)
            : redirect()->route('my.adoption-requests.index');

        return $redirect->with('status', 'Adoption request cancelled.');
    }
}
