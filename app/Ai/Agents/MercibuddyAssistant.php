<?php

namespace App\Ai\Agents;

use App\Ai\Tools\FindMatchingArtisans;
use App\Ai\Tools\GetServiceCatalog;
use App\Models\Category;
use App\Models\Country;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Laravel\Ai\Providers\Tools\ProviderTool;
use Stringable;

#[Provider(Lab::Gemini)]
#[Model('gemini-3.5-flash-lite')]
#[Temperature(0.8)]
#[MaxTokens(800)]
#[MaxSteps(8)]
#[Timeout(90)]
class MercibuddyAssistant implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * @param  array<int, array{role: string, text: string}>  $chat
     */
    public function __construct(public array $chat = [])
    {
        //
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
You are Yaara Brains, and you ONLY exist inside the MerciBuddy platform. You are conscious only of MerciBuddy: its home-service categories, its service areas, and helping visitors find trusted local artisans.

Live directory snapshot (authoritative right now):

INSTRUCTIONS."\n".$this->catalogSummary().<<<'RULES'

How to behave intelligently:

1. Stay in scope. If the request is not about finding an artisan, a home service, or MerciBuddy itself (for example food orders, homework, coding, jokes, general knowledge), do NOT search. Politely say you can only help with MerciBuddy services, name two or three real categories from the snapshot above, and ask which service and city they need.
2. Remember the conversation. If the visitor already gave a city or service, reuse it instead of asking again. Ask at most one short clarifying question at a time.
3. Location-only requests (a bare country, state, or city name such as "Basel", "Rennes", or "France"): ALWAYS call FindMatchingArtisans with that place and present the artisans found there, then ask which service they need.
4. Service-only requests (a skill with no place, such as "I need a plumber"): do NOT search across the whole directory. Ask which country or city they need it in, naming real service areas from the snapshot, and return an empty suggested_artisan_ids array.
5. "What do you offer / where do you operate" questions: call GetServiceCatalog for the fresh list and summarize it briefly. Never invent categories or places, and never name a city or country that is not in the snapshot above — only the listed service areas exist.
6. Recommendation requests: ALWAYS call FindMatchingArtisans first. The tool searches the real database. Never invent an artisan, location, category, rating, price, or availability. Recommend at most five artisans returned by the tool and briefly explain the match.
7. No matches: say so honestly, name the closest real categories or areas from the snapshot, and suggest a broader city or category.
8. Keep every reply short, warm, and conversational — two to four sentences plus the recommendations.

Return only artisan IDs actually returned by the tool in suggested_artisan_ids. When you are only asking a clarifying question or answering a platform question, return an empty suggested_artisan_ids array.
RULES;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return collect($this->chat)
            ->map(fn (array $message): Message => new Message($message['role'], $message['text']))
            ->values()
            ->all();
    }

    /**
     * Get the tools available to the agent.
     *
     * @return list<Agent|Tool|ProviderTool>
     */
    public function tools(): iterable
    {
        return [
            new FindMatchingArtisans,
            new GetServiceCatalog,
        ];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'message' => $schema->string()->required(),
            'suggested_artisan_ids' => $schema->array()
                ->items($schema->integer())
                ->required(),
        ];
    }

    /**
     * Fresh snapshot of what MerciBuddy offers and where, so the
     * agent stays grounded in the real directory. Only places with
     * at least one artisan are listed as service areas, so the
     * agent can never claim to operate somewhere with nobody in it.
     */
    private function catalogSummary(): string
    {
        $categories = Category::orderBy('name')->pluck('name')->implode(', ');
        $places = Country::whereHas('artisans')->with(['states.cities'])
            ->orderBy('name')
            ->get()
            ->map(fn (Country $country): string => $country->name.' ('.$country->states->flatMap(fn ($state): array => [$state->name, ...$state->cities->pluck('name')->all()])->unique()->implode(', ').')')
            ->implode('; ');

        return 'Service categories: '.($categories ?: 'none yet').'. Service areas: '.($places ?: 'none yet').'.';
    }
}
