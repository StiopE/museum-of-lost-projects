<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});

test('visitors can find the public about page from the museum', function () {
    $this->get(route('home'))->assertSee('href="'.route('about').'"', false);
    $this->get(route('about'))->assertOk()->assertSeeText('How adoption works');
});
