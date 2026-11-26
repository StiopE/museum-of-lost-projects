<?php

use App\Enums\ProjectStatus;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('the dashboard gives a new user useful next steps instead of starter placeholders', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Welcome back, '.$user->name)
        ->assertSeeText('Your museum collection starts here')
        ->assertSee(route('my.projects.create'), false)
        ->assertSee(route('projects.index'), false)
        ->assertSee('aria-label="Museum of Failed Projects home"', false)
        ->assertDontSee('placeholder-pattern');
});

test('the dashboard shows the users project counts and recent work', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    Project::factory()->for($user, 'creator')->for($category)->create([
        'title' => 'A project in progress',
        'status' => ProjectStatus::Draft,
    ]);
    Project::factory()->for($user, 'creator')->for($category)->published()->create([
        'title' => 'A shared museum project',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Total projects')
        ->assertSeeText('Drafts')
        ->assertSeeText('Published')
        ->assertSeeText('2')
        ->assertSeeText('A project in progress')
        ->assertSeeText('A shared museum project');
});

test('the museum home links members back to their dashboard and account', function () {
    $this->get(route('home'))->assertDontSee('href="'.route('dashboard').'"', false);

    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertOk()
        ->assertSee('href="'.route('dashboard').'"', false)
        ->assertSee('href="'.route('profile.edit').'"', false);
});

test('editing an older project brings it back into the five recent projects', function () {
    $creator = User::factory()->create();
    $older = Project::factory()->for($creator, 'creator')->create([
        'created_at' => now()->subMonth(),
        'updated_at' => now()->subMonth(),
    ]);
    Project::factory()->count(5)->for($creator, 'creator')->create([
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    $older->update(['title' => 'New plans for the sky atlas']);

    $this->actingAs($creator)->get(route('dashboard'))
        ->assertViewHas('projects', fn ($projects) => $projects->count() === 5 && $projects->first()->is($older));
});
