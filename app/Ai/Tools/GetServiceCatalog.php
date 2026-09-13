<?php

namespace App\Ai\Tools;

use App\Models\Category;
use App\Models\Country;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetServiceCatalog implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get the live MerciBuddy service catalog: every service category offered and every country, state, and city served. Use it to answer questions about what MerciBuddy offers and where, and to check whether a requested service or place exists before searching.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $categories = Category::orderBy('name')->pluck('name')->values();

        $places = Country::with(['states.cities'])
            ->orderBy('name')
            ->get()
            ->map(fn (Country $country): array => [
                'country' => $country->name,
                'states' => $country->states->map(fn ($state): array => [
                    'state' => $state->name,
                    'cities' => $state->cities->pluck('name')->values()->all(),
                ])->all(),
            ])
            ->all();

        return json_encode([
            'categories' => $categories,
            'service_areas' => $places,
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
