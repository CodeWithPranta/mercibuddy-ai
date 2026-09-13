<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

function disableChatAi(): void
{
    // The dev .env carries live provider keys; null them so the stream
    // takes the instant local path instead of calling real models.
    config()->set('ai.providers.gemini.key', null);
    config()->set('ai.providers.groq.key', null);
}

test('chat streams live tokens and finishes with artisan cards', function () {
    disableChatAi();
    $seeded = seedConciergeDirectory();

    $response = $this->withoutMiddleware(VerifyCsrfToken::class)
        ->postJson('/assistant/stream', ['question' => 'Basel']);

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');

    $stream = $response->streamedContent();

    expect($stream)->toContain('event: token')
        ->and($stream)->toContain('event: done')
        ->and($stream)->toContain($seeded['luca']->full_name);

    $messages = session('assistant_chat');
    expect($messages)->not->toBeEmpty()
        ->and($messages[array_key_last($messages)]['role'])->toBe('assistant')
        ->and($messages[array_key_last($messages)]['text'])->toContain('Basel');
});

test('suggested artisan cards always carry a working profile link', function () {
    disableChatAi();
    seedConciergeDirectory();

    $stream = $this->withoutMiddleware(VerifyCsrfToken::class)
        ->postJson('/assistant/stream', ['question' => 'Basel'])
        ->streamedContent();

    preg_match('/event: done\ndata: (.*)\n/', $stream, $matches);

    expect($matches)->not->toBeEmpty();

    $artisans = json_decode($matches[1], true)['artisans'] ?? [];

    expect($artisans)->not->toBeEmpty();

    foreach ($artisans as $artisan) {
        expect($artisan['profile_url'] ?? '')->toContain('/artisan/');
    }
});

test('chat stream validates the question', function () {
    $response = $this->withoutMiddleware(VerifyCsrfToken::class)
        ->postJson('/assistant/stream', ['question' => 'x']);

    $response->assertStatus(422)->assertJsonValidationErrors('question');
});

test('chat can be cleared', function () {
    session()->put('assistant_chat', [['role' => 'user', 'text' => 'hi']]);

    $response = $this->withoutMiddleware(VerifyCsrfToken::class)
        ->postJson('/assistant/clear', []);

    $response->assertOk()->assertJson(['cleared' => true]);
    expect(session('assistant_chat'))->toBeNull();
});

test('landing page renders the JS chat widget', function () {
    seedConciergeDirectory();

    $this->get('/')->assertOk()
        ->assertSee('yaara-chat', false)
        ->assertSee('assistant/stream', false)
        ->assertSee('Describe what you need..', false);
});
