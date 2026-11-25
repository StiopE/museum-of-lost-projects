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
