<?php

namespace App\Ai;

use App\Ai\Tools\FindMatchingArtisans;
use App\Models\Artisan;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Laravel\Ai\Tools\Request;

class LocalConcierge
{
    /**
     * @param  array<int, array{role: string, text: string}>  $history  Prior chat, oldest first.
     */
    public function __construct(private array $history = []) {}

    /**
     * Answer without calling an external model. Grounded in the live directory.
     *
     * @return array{0: array<int>, 1: string} Artisan IDs plus the reply text.
     */
    public function answer(string $question): array
    {
        return match ($this->detectIntent($question)) {
            'greeting' => [[], $this->greetingResponse($question)],
            'platform' => [[], $this->platformResponse()],
            'location-only' => $this->placeSearchResponse($question),
            'service-only' => [[], $this->serviceOnlyResponse($question)],
            'off-topic' => [[], $this->offTopicResponse()],
            default => $this->combinedSearchResponse($question),
        };
    }

    /**
     * Classify the visitor's intent, reusing places and services already
     * mentioned earlier in the conversation.
     *
     * @return 'greeting'|'platform'|'service'|'service-only'|'location-only'|'off-topic'
     */
    public function detectIntent(string $question): string
    {
        $text = mb_strtolower($question);

        if (preg_match('/^(hi+|hello|hey|yo|good\s?(morning|afternoon|evening)|salam|assalamualaikum)\b/u', $text) === 1 && mb_strlen($text) < 40) {
            return 'greeting';
        }

        if (str_contains($text, 'mercibuddy')
            || str_contains($text, 'yaara')
            || (str_contains($text, 'what') && (str_contains($text, 'service') || str_contains($text, 'offer') || str_contains($text, 'do you')))
            || str_contains($text, 'how does')
            || str_contains($text, 'how it works')
            || str_contains($text, 'about')) {
            return 'platform';
        }

        $service = $this->matchedService($question) ?? $this->matchedService($this->historyText());
        $place = $this->matchedPlace($question) ?? $this->matchedPlace($this->historyText());

        if ($service !== null && $place !== null) {
            return 'service';
        }

        if ($service !== null) {
            return 'service-only';
        }

        if ($place !== null) {
            // A bare place ("Basel") lists local artisans, but a place next
            // to any trade word ("cook in Basel") is a service search, so an
            // unoffered trade gets an honest "we don't offer that" reply.
            if ($this->hasAnyServiceSignal($question)) {
                return 'service';
            }

            return 'location-only';
        }

        if ($this->hasServiceOverlap($question) || $this->hasServiceVerb($question)) {
            return 'service';
        }

        return 'off-topic';
    }

    /**
     * A location on its own ("Basel", "Rennes", "France") lists the trusted
     * artisans working there instead of asking another question.
     *
     * @return array{0: array<int>, 1: string}
     */
    private function placeSearchResponse(string $question): array
    {
        $place = $this->matchedPlace($question) ?? $this->matchedPlace($this->historyText());
        $label = $place !== null ? $this->displayPlace($place) : 'your area';

        $ids = $this->searchIds((string) $place);

        if ($ids === []) {
            return [[], $this->pick([
                'We cover '.$label.'. Which service do you need there? We offer '.$this->categoryList().'.',
                $label.' is covered — tell me which service you need ('.$this->categoryList().') and I will find the right people.',
            ])];
        }

        $names = Artisan::query()->whereIn('id', $ids)->pluck('full_name')->implode(', ');

        return [$ids, $this->pick([
            'I found trusted artisans in '.$label.': '.$names.'. Which service do you need? We offer '.$this->categoryList().'.',
            'Good news — '.$names.' work in '.$label.'. Tell me which service you need and I will narrow it down.',
        ])];
    }

    /**
     * A service without a place ("I need a plumber") asks for the country
     * or city instead of guessing across the whole directory.
     */
    private function serviceOnlyResponse(string $question): string
    {
        $service = $this->matchedService($question) ?? $this->matchedService($this->historyText()) ?? 'that service';

        return $this->pick([
            'Got it — '.$service.'. Which country or city should I search in? Right now I cover '.$this->areaList().'.',
            'I can find '.$service.' for you. Just tell me the country or city, for example "in Basel" or "in Rennes".',
        ]);
    }

    /**
     * Service plus place, searched across the current message and everything
     * said before, so follow-ups like "in Rennes" keep working.
     *
     * @return array{0: array<int>, 1: string}
     */
    private function combinedSearchResponse(string $question): array
    {
        $ids = $this->searchIds($this->historyText().' '.$question);

        if ($ids !== []) {
            $artisans = Artisan::query()->with(['city', 'state', 'country'])->whereIn('id', $ids)->get();
            $names = $artisans->pluck('full_name')->implode(', ');
            $place = $artisans->firstWhere(fn (Artisan $artisan): bool => filled($artisan->city?->name || $artisan->state?->name || $artisan->country?->name));
            $location = $place
                ? collect([$place->city?->name, $place->state?->name, $place->country?->name])->filter()->implode(', ')
                : '';
            $suffix = $location !== '' ? ' in '.$location : '';

            return [$ids, $this->pick([
                "I found {$names}, a trusted professional{$suffix}, from the MerciBuddy directory.",
                "Good news — {$names} matches your request{$suffix}. I have added the profile card below.",
            ])];
        }

        if ($this->hasServiceOverlap($this->historyText().' '.$question)) {
            return [[], $this->pick([
                'I could not find that service in the requested area right now. Try a nearby city, or pick from what we offer: '.$this->categoryList().'.',
                'No match in that area this time. Broaden the city a little, or choose from '.$this->categoryList().', and I will search again.',
            ])];
        }

        return [[], $this->noServiceOfferedResponse()];
    }

    /**
     * @return array<int>
     */
    private function searchIds(string $search): array
    {
        $fallback = json_decode(
            (string) (new FindMatchingArtisans)->handle(new Request(['search' => $search])),
            true,
        );

        return collect($fallback ?: [])
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->filter()
            ->take(5)
            ->values()
            ->all();
    }

    /**
     * Match the request against a service MerciBuddy actually offers and
     * return its category name.
     */
    private function matchedService(string $question): ?string
    {
        $text = mb_strtolower($question);
        $categories = Category::orderByRaw('LENGTH(name) DESC')->pluck('name');

        foreach ($categories as $name) {
            if (str_contains($text, mb_strtolower($name))) {
                return $name;
            }
        }

        $synonyms = [
            'Beauty and Wellness' => ['beauty', 'wellness', 'spa', 'salon', 'massage', 'beautician'],
            'Childcare' => ['childcare', 'nanny', 'babysit', 'child'],
            'Cleaning' => ['clean', 'maid', 'housekeep', 'mop'],
            'Elderly and Disability Care' => ['elderly', 'disability', 'caregiver', 'carer'],
            'Fitness and Yoga' => ['fitness', 'yoga', 'gym', 'trainer', 'coach', 'instructor'],
            'Plumbing and Handyman' => ['plumb', 'tap', 'leak', 'fix', 'handyman', 'pipe', 'sink'],
            'Tutoring and Education' => ['tutor', 'science', 'math', 'physics', 'chemistry', 'biology', 'teach', 'lesson', 'school'],
            'Cooking and Catering' => ['cook', 'chef', 'cater', 'meal', 'kitchen'],
            'Electrical Services' => ['electric', 'wiring', 'appliance'],
            'Driving and Transport' => ['driv', 'taxi', 'transport', 'delivery'],
        ];

        foreach ($categories as $name) {
            foreach ($synonyms[$name] ?? [] as $signal) {
                // Word-prefix match so plurals and -ing forms count:
                // "tutors", "tutoring", "cleaning", "leaks" all match.
                if (preg_match('/\b'.preg_quote($signal, '/').'[a-z]*\b/u', $text) === 1) {
                    return $name;
                }
            }
        }

        return null;
    }

    /**
     * Does the text mention a service MerciBuddy actually offers?
     */
    private function hasServiceOverlap(string $text): bool
    {
        $text = mb_strtolower($text);

        foreach ($this->catalogSignals() as $signal) {
            if (str_contains($signal, ' ') ? str_contains($text, $signal) : $this->hasWord($text, $signal)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Any trade-like word, offered or not, so "cook in Basel" is treated as
     * a service search instead of a bare-place browse.
     */
    private function hasAnyServiceSignal(string $text): bool
    {
        $text = mb_strtolower($text);

        foreach ($this->serviceSignals() as $signal) {
            if ($this->hasWord($text, $signal)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Category name tokens plus close everyday synonyms for offered services.
     *
     * @return array<int, string>
     */
    private function catalogSignals(): array
    {
        $tokens = Category::pluck('name')
            ->flatMap(fn (string $name): array => preg_split('/[\s\-]+/', mb_strtolower($name)) ?: [])
            ->reject(fn (string $token): bool => in_array($token, ['and', 'of', 'for'], true));

        return $tokens
            ->merge([
                'beautician', 'spa', 'salon', 'massage', 'wellness',
                'nanny', 'babysitter', 'babysitting',
                'cleaner', 'housekeeper', 'maid', 'housekeeping',
                'caregiver', 'carer', 'caretaker',
                'trainer', 'coach', 'gym', 'yoga', 'fitness', 'instructor',
                'plumber', 'plumbing', 'tap', 'leak', 'fix', 'handyman',
                'tutor', 'tutoring', 'science', 'math', 'physics', 'chemistry', 'biology',
                'cook', 'cooking', 'chef', 'catering', 'meal', 'kitchen',
                'electrician', 'electrical', 'electric', 'wiring', 'appliance',
                'driver', 'driving', 'taxi', 'transport', 'delivery',
            ])
            ->unique()
            ->values()
            ->all();
    }

    /**
     * All searchable service words: catalog signals plus other common
     * trades worth checking against the artisan directory.
     *
     * @return array<int, string>
     */
    private function serviceSignals(): array
    {
        return collect($this->catalogSignals())
            ->merge([
                'carpenter', 'painter', 'mechanic', 'nurse', 'mopping',
            ])
            ->unique()
            ->values()
            ->all();
    }

    private function hasServiceVerb(string $text): bool
    {
        $text = mb_strtolower($text);

        return $this->hasWord($text, 'need')
            || $this->hasWord($text, 'needs')
            || $this->hasWord($text, 'find')
            || $this->hasWord($text, 'looking')
            || $this->hasWord($text, 'search')
            || $this->hasWord($text, 'want')
            || $this->hasWord($text, 'hire')
            || $this->hasWord($text, 'book')
            || $this->hasWord($text, 'help');
    }

    private function hasWord(string $text, string $word): bool
    {
        return preg_match('/\b'.preg_quote($word, '/').'\b/u', mb_strtolower($text)) === 1;
    }

    /**
     * Known place names from the directory, longest first.
     *
     * @return array<int, string>
     */
    private function placeSignals(): array
    {
        return Country::pluck('name')
            ->merge(State::pluck('name'))
            ->merge(City::pluck('name'))
            ->map(fn (string $name): string => mb_strtolower($name))
            ->unique()
            ->sortByDesc(fn (string $name): int => mb_strlen($name))
            ->values()
            ->all();
    }

    private function matchedPlace(string $text): ?string
    {
        $text = mb_strtolower($text);

        foreach ($this->placeSignals() as $place) {
            if (str_contains($place, ' ') || str_contains($place, '-') ? str_contains($text, $place) : $this->hasWord($text, $place)) {
                return $place;
            }
        }

        return null;
    }

    private function displayPlace(string $place): string
    {
        return collect(explode(' ', (string) $place))
            ->map(fn (string $word): string => mb_convert_case($word, MB_CASE_TITLE))
            ->implode(' ');
    }

    private function historyText(): string
    {
        return collect($this->history)->pluck('text')->implode("\n");
    }

    private function assistantReplyCount(): int
    {
        return count(array_filter(
            $this->history,
            fn (array $message): bool => ($message['role'] ?? '') === 'assistant'
        ));
    }

    /**
     * Deterministic rotation so repeated situations never read identically.
     *
     * @param  array<int, string>  $variants
     */
    private function pick(array $variants): string
    {
        return $variants[$this->assistantReplyCount() % count($variants)];
    }

    private function categoryList(): string
    {
        return Category::orderBy('name')->pluck('name')->implode(', ');
    }

    private function areaList(): string
    {
        // Only claim places where artisans actually exist, so the bot
        // never advertises a city or country with nobody in it.
        return Country::whereHas('artisans')
            ->orderBy('name')
            ->pluck('name')
            ->implode(', ');
    }

    /**
     * Mirror the visitor's salutation and never repeat the same line:
     * first greeting introduces Yaara Brains, later ones stay short
     * and rotate so the chat feels alive.
     */
    private function greetingResponse(string $question): string
    {
        $text = mb_strtolower($question);
        $salutation = 'Hello';

        if (str_contains($text, 'morning')) {
            $salutation = 'Good morning';
        } elseif (str_contains($text, 'afternoon')) {
            $salutation = 'Good afternoon';
        } elseif (str_contains($text, 'evening')) {
            $salutation = 'Good evening';
        }

        if ($this->assistantReplyCount() === 0) {
            return $salutation."! I'm Yaara Brains. Please tell me what you need and I'll help you find trusted skilled artisans near you.";
        }

        return $this->pick([
            'Still here and happy to help — just tell me the service you need and your city.',
            $salutation.' again! What service and city can I help you with today?',
            $salutation.'! Let me know the service and city, and I will find trusted artisans for you.',
        ]);
    }

    private function platformResponse(): string
    {
        return $this->pick([
            'Yaara Brains connects you with trusted local artisans. We currently offer '.$this->categoryList().', serving '.$this->areaList().'. Tell me what you need and where, and I will find the right people.',
            'Here is what MerciBuddy covers: '.$this->categoryList().'. Our service areas are '.$this->areaList().'. Give me a service and a city and I will find matching artisans.',
        ]);
    }

    private function offTopicResponse(): string
    {
        return $this->pick([
            "I'm Yaara Brains, so I can only help with finding trusted artisans and home services here — like ".$this->categoryList().'. Tell me what service you need and your city, and I will find the right people for you.',
            'That is outside what I can do — I live inside MerciBuddy and only know our artisans and services, such as '.$this->categoryList().'. What home service can I find for you, and in which city?',
        ]);
    }

    private function noServiceOfferedResponse(): string
    {
        return $this->pick([
            'We do not offer that service on MerciBuddy yet. We currently offer '.$this->categoryList().', serving '.$this->areaList().'. Which of these do you need, and in which city?',
            'That service is not on MerciBuddy right now — our directory covers '.$this->categoryList().'. Tell me which one fits and your city, and I will search right away.',
        ]);
    }
}
