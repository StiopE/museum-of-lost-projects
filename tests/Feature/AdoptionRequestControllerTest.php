<?php

use App\Enums\AdoptionRequestStatus;
use App\Enums\ProjectStatus;
use App\Models\AdoptionRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

test('an explorer can request an available published project', function () {
    $creator = User::factory()->create();
    $explorer = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->availableForAdoption()->create();

    $response = $this->actingAs($explorer)->post(route('projects.adoption-requests.store', $project), [
        'message' => 'I would like to continue this project with a smaller first release.',
    ]);

    $response->assertRedirectToRoute('projects.show', $project);
    $this->assertDatabaseHas('adoption_requests', [
        'project_id' => $project->id,
        'user_id' => $explorer->id,
        'status' => AdoptionRequestStatus::Pending->value,
    ]);
});

test('a creator cannot request adoption of their own project', function () {
    $creator = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->availableForAdoption()->create();

    $this->actingAs($creator)
        ->post(route('projects.adoption-requests.store', $project), [
            'message' => 'I want to adopt my own project.',
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('adoption_requests', 0);
});

test('an unavailable project cannot receive an adoption request', function () {
    $creator = User::factory()->create();
    $explorer = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->published()->create();

    $this->actingAs($explorer)
        ->post(route('projects.adoption-requests.store', $project), [
            'message' => 'I would like to continue this idea.',
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('adoption_requests', 0);
});

test('an adoption request needs a message within the allowed length', function () {
    $creator = User::factory()->create();
    $explorer = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->availableForAdoption()->create();

    $response = $this->actingAs($explorer)->post(route('projects.adoption-requests.store', $project), [
        'message' => str_repeat('a', 3001),
    ]);

    $response->assertSessionHasErrors('message');
    $this->assertDatabaseCount('adoption_requests', 0);
});

test('the project creator can approve a request and receive a linked revived draft', function () {
    $creator = User::factory()->create();
    $explorer = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->availableForAdoption()->create();
    $request = AdoptionRequest::factory()->for($project)->for($explorer, 'requester')->create();
    $otherPendingRequest = AdoptionRequest::factory()->for($project)->create();

    $response = $this->actingAs($creator)->patch(route('my.adoption-requests.approve', $request));

    $response->assertRedirectToRoute('my.projects.edit', $project);
    $this->assertDatabaseHas('adoption_requests', [
        'id' => $request->id,
        'status' => AdoptionRequestStatus::Approved->value,
    ]);
    $this->assertDatabaseHas('adoption_requests', [
        'id' => $otherPendingRequest->id,
        'status' => AdoptionRequestStatus::Rejected->value,
    ]);
    $this->assertDatabaseHas('projects', [
        'parent_project_id' => $project->id,
        'user_id' => $explorer->id,
        'title' => $project->title,
        'status' => ProjectStatus::Draft->value,
        'adoption_available' => false,
    ]);
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'status' => ProjectStatus::Published->value,
        'adoption_available' => false,
    ]);
});

test('an explorer can reach the revived draft after the original closes to adoption', function () {
    $request = AdoptionRequest::factory()->create();
    $this->actingAs($request->project->creator)->patch(route('my.adoption-requests.approve', $request));
    $request->refresh();
    $editLink = route('my.projects.edit', $request->revived_project_id);

    $this->actingAs($request->requester)->get(route('projects.show', $request->project))
        ->assertSeeText('Your request is approved.')
        ->assertSee('href="'.$editLink.'"', false)
        ->assertDontSeeText('Send adoption request');
    $this->get($editLink)->assertOk()
        ->assertSeeText('Revival of')
        ->assertSee('href="'.route('projects.show', $request->project).'"', false);
    $this->get(route('my.projects.index'))->assertSeeText('Revived · draft');
    $this->actingAs(User::factory()->create())->get(route('projects.show', $request->project))
        ->assertDontSee($editLink, false);
});

test('an explorer cannot approve their own adoption request', function () {
    $creator = User::factory()->create();
    $explorer = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->availableForAdoption()->create();
    $request = AdoptionRequest::factory()->for($project)->for($explorer, 'requester')->create();

    $this->actingAs($explorer)
        ->patch(route('my.adoption-requests.approve', $request))
        ->assertForbidden();

    $this->assertDatabaseHas('adoption_requests', [
        'id' => $request->id,
        'status' => AdoptionRequestStatus::Pending->value,
    ]);
    $this->assertDatabaseMissing('projects', [
        'parent_project_id' => $project->id,
    ]);
});

test('the project creator can reject an adoption request', function () {
    $creator = User::factory()->create();
    $explorer = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->availableForAdoption()->create();
    $request = AdoptionRequest::factory()->for($project)->for($explorer, 'requester')->create();

    $response = $this->actingAs($creator)->patch(route('my.adoption-requests.reject', $request));

    $response->assertRedirectToRoute('my.projects.edit', $project);
    $this->assertDatabaseHas('adoption_requests', [
        'id' => $request->id,
        'status' => AdoptionRequestStatus::Rejected->value,
    ]);
    $this->assertDatabaseMissing('projects', [
        'parent_project_id' => $project->id,
    ]);
});

test('an explorer can cancel their pending adoption request', function () {
    $creator = User::factory()->create();
    $explorer = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->availableForAdoption()->create();
    $request = AdoptionRequest::factory()->for($project)->for($explorer, 'requester')->create();

    $response = $this->actingAs($explorer)->patch(route('my.adoption-requests.cancel', $request));

    $response->assertRedirectToRoute('projects.show', $project);
    $this->assertDatabaseHas('adoption_requests', [
        'id' => $request->id,
        'status' => AdoptionRequestStatus::Cancelled->value,
    ]);
});

test('an explorer cannot submit a second pending request for the same project', function () {
    $creator = User::factory()->create();
    $explorer = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->availableForAdoption()->create();
    AdoptionRequest::factory()->for($project)->for($explorer, 'requester')->create();

    $response = $this->actingAs($explorer)->post(route('projects.adoption-requests.store', $project), [
        'message' => 'I am submitting a duplicate request.',
    ]);

    $response->assertSessionHasErrors('message');
    $this->assertDatabaseCount('adoption_requests', 1);
});

test('the dashboard leads creators and explorers to their own requests and the resulting revival', function () {
    $request = AdoptionRequest::factory()->create();
    $other = AdoptionRequest::factory()->create();
    $creator = $request->project->creator;
    $explorer = $request->requester;

    $this->get(route('my.adoption-requests.index'))->assertRedirectToRoute('login');
    $this->actingAs($creator)->get(route('dashboard'))
        ->assertSeeText('One request awaits your review.')
        ->assertSee(route('my.adoption-requests.index'), false);
    $this->get(route('my.adoption-requests.index'))
        ->assertSeeText($request->message)
        ->assertSee(route('my.projects.edit', $request->project).'#request-'.$request->id, false)
        ->assertDontSeeText($other->message);
    $this->patch(route('my.adoption-requests.approve', $request))->assertRedirect();
    $editLink = route('my.projects.edit', $request->refresh()->revived_project_id);

    $this->actingAs($explorer)->get(route('dashboard'))->assertSee($editLink, false);
    $this->get(route('my.adoption-requests.index'))->assertSeeText('Approved')
        ->assertSee($editLink, false)->assertDontSeeText($other->message);
});

test('an explorer can withdraw from the workspace after the original is archived', function () {
    $request = AdoptionRequest::factory()->create();
    $this->actingAs($request->project->creator)->patch(route('my.projects.archive', $request->project));
    $this->actingAs($request->requester)->get(route('my.adoption-requests.index'))
        ->assertSeeText('Archived project')->assertDontSeeText($request->project->title)
        ->assertSee(route('my.adoption-requests.cancel', $request), false);
    $this->patch(route('my.adoption-requests.cancel', $request))->assertRedirectToRoute('my.adoption-requests.index');
    expect($request->refresh()->status)->toBe(AdoptionRequestStatus::Cancelled);
});

test('a submitted proposal emails only its creator with a link to the request', function () {
    $project = Project::factory()->availableForAdoption()->create();
    $explorer = User::factory()->create();
    $url = route('projects.adoption-requests.store', $project);
    $this->actingAs($explorer)->post($url, ['message' => 'I can build a smaller prototype.'])->assertRedirect();
    $this->post($url, ['message' => 'A duplicate proposal.'])->assertSessionHasErrors('message');

    $request = $project->adoptionRequests()->sole();
    $mail = Mail::mailer('array')->getSymfonyTransport()->messages()->sole()->getOriginalMessage();
    expect($mail->getTo())->toHaveCount(1)
        ->and($mail->getTo()[0]->getAddress())->toBe($project->creator->email)
        ->and($mail->getSubject())->toContain($project->title)
        ->and($mail->getHtmlBody())->toContain(route('my.projects.edit', $project).'#request-'.$request->id);
});

test('a rolled back adoption request leaves no email waiting for delivery', function () {
    $project = Project::factory()->availableForAdoption()->create();
    $explorer = User::factory()->create();

    DB::beginTransaction();
    try {
        $this->actingAs($explorer)->post(route('projects.adoption-requests.store', $project), [
            'message' => 'I can revive the prototype.',
        ])->assertRedirect();
    } finally {
        DB::rollBack();
    }

    $this->assertDatabaseCount('adoption_requests', 0);
    expect(Mail::mailer('array')->getSymfonyTransport()->messages())->toBeEmpty();
});

test('approval emails the revival link only to the adopter and informs the remaining pending explorers', function () {
    $chosen = AdoptionRequest::factory()->create();
    $other = AdoptionRequest::factory()->for($chosen->project)->create();
    AdoptionRequest::factory()->for($chosen->project)->create(['status' => AdoptionRequestStatus::Cancelled]);

    $this->actingAs($chosen->project->creator)->patch(route('my.adoption-requests.approve', $chosen))->assertRedirect();
    $this->patch(route('my.adoption-requests.approve', $chosen))->assertSessionHasErrors('adoption_request');

    $emails = Mail::mailer('array')->getSymfonyTransport()->messages()
        ->map(fn ($sent) => $sent->getOriginalMessage())
        ->keyBy(fn ($email) => $email->getTo()[0]->getAddress());
    $draftUrl = route('my.projects.edit', $chosen->refresh()->revived_project_id);
    expect($emails->keys()->all())->toEqualCanonicalizing([$chosen->requester->email, $other->requester->email]);
    expect($emails[$chosen->requester->email]->getHtmlBody())->toContain($draftUrl);
    expect($emails[$other->requester->email]->getHtmlBody())
        ->toContain(route('my.adoption-requests.index'))->not->toContain($draftUrl);
    expect(Mail::mailer('array')->getSymfonyTransport()->messages())->toHaveCount(2);
});

test('rejecting an archived project request emails its requester without exposing the private title', function () {
    $request = AdoptionRequest::factory()->create();
    $this->actingAs($request->project->creator)->patch(route('my.projects.archive', $request->project));
    $this->patch(route('my.adoption-requests.reject', $request))->assertRedirect();

    $email = Mail::mailer('array')->getSymfonyTransport()->messages()->sole()->getOriginalMessage();
    expect($email->getTo()[0]->getAddress())->toBe($request->requester->email)
        ->and($email->getSubject())->toBe('Adoption request declined')
        ->and($email->getHtmlBody())->toContain(route('my.adoption-requests.index'))->not->toContain($request->project->title);
});
