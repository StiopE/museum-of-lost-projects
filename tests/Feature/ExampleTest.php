<?php

use App\Enums\ProjectStatus;
use App\Models\Category;
use App\Models\Project;

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});

test('visitors can find the public about page from the museum', function () {
    $this->get(route('home'))->assertSee('href="'.route('about').'"', false);
    $this->get(route('about'))->assertOk()->assertSeeText('How adoption works');
});

test('the museum counts published exhibits and features its four newest entries', function () {
    $category = Category::factory()->create(['slug' => 'art-design']);
    $older = Project::factory()->for($category)->published()->create(['created_at' => now()->subDay()]);
    $recent = Project::factory()->count(4)->for($category)->published()->create();
    $draft = Project::factory()->for($category)->create();

    $this->get(route('home'))->assertOk()
        ->assertSeeText('View all 5 exhibits')
        ->assertSeeTextInOrder($recent->reverse()->pluck('title')->all())
        ->assertDontSeeText($older->title)
        ->assertDontSeeText($draft->title)
        ->assertSee(route('projects.index', ['category' => $category->slug]), false);
});

test('the illustrated press links to its exhibit only while published', function () {
    $press = Project::factory()->create(['title' => 'Rain Ticket Press']);
    $this->get(route('home'))->assertDontSee(route('projects.show', $press), false);

    $press->update(['status' => ProjectStatus::Published]);
    $this->get(route('home'))->assertSee('aria-label="View the Rain Ticket Press exhibit"', false);
});
