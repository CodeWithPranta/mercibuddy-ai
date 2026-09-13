<?php

namespace App\Ai;

use App\Ai\Agents\MercibuddyAssistant as MercibuddyAgent;
use App\Models\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Exceptions\RateLimitedException;
use Throwable;

/**
 * Single place that turns a visitor question into a reply plus artisan
 * cards. Used by the streaming endpoint and the legacy Livewire chat,
 * so both behave identically.
 */
class ChatResponder
{
    /**
     * @param  array<int, array{role: string, text: string}>  $history  Prior chat, oldest first.
     */
    public function __construct(private array $history = []) {}

    /**
     * @return array{message: string, artisans: array<int, array<string, mixed>>}
     */
    public function respond(string $question, bool $useAi = true): array
    {
        $question = trim($question);

        if ($useAi && $this->hasAiCredentials()) {
            try {
                // Gemini first: a real conversation with memory of this chat.
                $response = $this->promptAgent($question);

                $artisans = $this->recommendationsFor($response['suggested_artisan_ids'] ?? []);
                $answer = (string) ($response['message'] ?? '');

                if ($answer !== '') {
                    return ['message' => $answer, 'artisans' => $artisans];
                }

                [$fallbackArtisans, $fallbackAnswer] = $this->answerLocally($question);

                return ['message' => $fallbackAnswer, 'artisans' => $fallbackArtisans];
            } catch (Throwable $exception) {
                Log::error('MerciBuddy assistant request failed.', [
                    'exception' => $exception,
                ]);
            }
        }

        [$artisans, $answer] = $this->answerLocally($question);

        return ['message' => $answer, 'artisans' => $artisans];
    }

    /**
     * Skip the model call entirely when no provider key is configured,
     * so misconfigured environments and tests fall back instantly
     * instead of waiting on doomed HTTP attempts.
     */
    private function hasAiCredentials(): bool
    {
        return filled(config('ai.providers.gemini.key'))
            || filled(config('ai.providers.groq.key'));
    }

    /**
     * Prompt Gemini on the pinned model, then fail over across separate
     * quota buckets: backup Gemini model, then Groq (free tier) when a
     * key is configured.
     *
     * @return array{message?: string, suggested_artisan_ids?: array<int>}
     */
    private function promptAgent(string $question): array
    {
        try {
            return (new MercibuddyAgent($this->history))->prompt($question)->toArray();
        } catch (RateLimitedException $exception) {
            Log::warning('MerciBuddy assistant rate-limited, failing over to backup Gemini model.', [
                'exception' => $exception,
            ]);
        }

        try {
            return (new MercibuddyAgent($this->history))
                ->prompt($question, model: 'gemini-flash-latest')
                ->toArray();
        } catch (RateLimitedException $exception) {
            Log::warning('Backup Gemini model rate-limited, failing over to Groq.', [
                'exception' => $exception,
            ]);
        }

        if (filled(config('ai.providers.groq.key'))) {
            return (new MercibuddyAgent($this->history))
                ->prompt($question, provider: [Lab::Groq])
                ->toArray();
        }

        throw new RateLimitedException('All configured AI providers are rate-limited and no Groq key is set.');
    }

    /**
     * Local intelligence used when Gemini is unreachable, or when it
     * returns an empty reply. Grounded in the live directory, with
     * conversation memory handled by the concierge.
     *
     * @return array{0: array<int, array<string, mixed>>, 1: string}
     */
    private function answerLocally(string $question): array
    {
        [$artisanIds, $answer] = (new LocalConcierge($this->history))->answer($question);

        return [$this->recommendationsFor($artisanIds), $answer];
    }

    /**
     * @param  iterable<int|string>  $artisanIds
     * @return array<int, array<string, mixed>>
     */
    private function recommendationsFor(iterable $artisanIds): array
    {
        $artisanIds = collect($artisanIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter()
            ->take(5)
            ->values();

        return Artisan::query()
            ->with(['category', 'country', 'state', 'city'])
            ->whereIn('id', $artisanIds)
            ->get()
            ->sortBy(fn (Artisan $artisan): int => $artisanIds->search($artisan->id))
            ->values()
            ->map(fn (Artisan $artisan): array => [
                'id' => $artisan->id,
                'name' => $artisan->full_name,
                'photo' => str_starts_with((string) $artisan->profile_photo, 'http')
                    ? $artisan->profile_photo
                    : asset('storage/'.$artisan->profile_photo),
                'profession' => $artisan->profession,
                'category' => $artisan->category?->name,
                'experience_years' => $artisan->experience_in_year,
                'location' => collect([$artisan->city?->name, $artisan->state?->name, $artisan->country?->name])
                    ->filter()
                    ->implode(', '),
                'profile_url' => route('artisan.show', [
                    'artisan' => $artisan->id,
                    'slug' => Str::slug($artisan->full_name),
                ]),
            ])->all();
    }
}
