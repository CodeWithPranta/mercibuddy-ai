<?php

namespace Database\Seeders;

use DateTimeInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('countries_states_citiesSQL');

        $countries = $this->parseCountries($path.'/countries.sql');
        $states = $this->parseStates($path.'/states.sql');
        $cities = $this->parseCities($path.'/cities.sql');

        $now = now();

        DB::transaction(function () use ($countries, $states, $cities, $now): void {
            $countryMap = $this->importCountries($countries, $now);
            $stateMap = $this->importStates($states, $countryMap, $now);
            $this->importCities($cities, $stateMap, $now);
        });
    }

    /**
     * @return array<int, array{id: int, shortname: string, name: string, phonecode: int}>
     */
    private function parseCountries(string $path): array
    {
        preg_match_all(
            "/\((\d+),\s*'((?:[^']|'')*)',\s*'((?:[^']|'')*)',\s*(\d+)\)/",
            (string) file_get_contents($path),
            $matches,
            PREG_SET_ORDER,
        );

        return array_map(fn (array $match): array => [
            'id' => (int) $match[1],
            'shortname' => str_replace("''", "'", $match[2]),
            'name' => str_replace("''", "'", $match[3]),
            'phonecode' => (int) $match[4],
        ], $matches);
    }

    /**
     * @return array<int, array{id: int, name: string, country_id: int}>
     */
    private function parseStates(string $path): array
    {
        preg_match_all(
            "/\((\d+),\s*'((?:[^']|'')*)',\s*(\d+)\)/",
            (string) file_get_contents($path),
            $matches,
            PREG_SET_ORDER,
        );

        return array_map(fn (array $match): array => [
            'id' => (int) $match[1],
            'name' => str_replace("''", "'", $match[2]),
            'country_id' => (int) $match[3],
        ], $matches);
    }

    /**
     * @return array<int, array{id: int, name: string, state_id: int}>
     */
    private function parseCities(string $path): array
    {
        preg_match_all(
            "/\((\d+),\s*'((?:[^']|'')*)',\s*(\d+)\)/",
            (string) file_get_contents($path),
            $matches,
            PREG_SET_ORDER,
        );

        return array_map(fn (array $match): array => [
            'id' => (int) $match[1],
            'name' => str_replace("''", "'", $match[2]),
            'state_id' => (int) $match[3],
        ], $matches);
    }

    /**
     * @param  array<int, array{id: int, shortname: string, name: string, phonecode: int}>  $rows
     * @return array<int, int>
     */
    private function importCountries(array $rows, DateTimeInterface $now): array
    {
        $existing = [];

        foreach (DB::table('countries')->get(['id', 'name']) as $country) {
            $existing[Str::lower($country->name)] = (int) $country->id;
        }

        $usedSlugs = [];

        foreach (DB::table('countries')->pluck('slug') as $slug) {
            if (filled($slug)) {
                $usedSlugs[$slug] = true;
            }
        }

        $inserts = [];

        foreach ($rows as $row) {
            $nameKey = Str::lower($row['name']);

            if (isset($existing[$nameKey])) {
                continue;
            }

            $existing[$nameKey] = 0;

            $inserts[] = [
                'shortname' => $row['shortname'],
                'name' => $row['name'],
                'slug' => $this->uniqueSlug($row['name'], $usedSlugs),
                'phonecode' => $row['phonecode'] > 0 ? '+'.$row['phonecode'] : (string) $row['phonecode'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($inserts, 200) as $chunk) {
            DB::table('countries')->insert($chunk);
        }

        $ids = [];

        foreach (DB::table('countries')->get(['id', 'name']) as $country) {
            $ids[Str::lower($country->name)] = (int) $country->id;
        }

        $map = [];

        foreach ($rows as $row) {
            $map[$row['id']] = $ids[Str::lower($row['name'])];
        }

        return $map;
    }

    /**
     * @param  array<int, array{id: int, name: string, country_id: int}>  $rows
     * @param  array<int, int>  $countryMap
     * @return array<int, int>
     */
    private function importStates(array $rows, array $countryMap, DateTimeInterface $now): array
    {
        $existing = [];

        foreach (DB::table('states')->get(['id', 'country_id', 'name']) as $state) {
            $existing[$state->country_id.'|'.Str::lower($state->name)] = (int) $state->id;
        }

        $inserts = [];

        foreach ($rows as $row) {
            $countryId = $countryMap[$row['country_id']] ?? null;

            if (! $countryId) {
                continue;
            }

            $key = $countryId.'|'.Str::lower($row['name']);

            if (isset($existing[$key])) {
                continue;
            }

            $inserts[] = [
                'name' => $row['name'],
                'country_id' => $countryId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $existing[$key] = 0;
        }

        foreach (array_chunk($inserts, 500) as $chunk) {
            DB::table('states')->insert($chunk);
        }

        $lookup = [];

        foreach (DB::table('states')->get(['id', 'country_id', 'name']) as $state) {
            $lookup[$state->country_id.'|'.Str::lower($state->name)] = (int) $state->id;
        }

        $map = [];

        foreach ($rows as $row) {
            $countryId = $countryMap[$row['country_id']] ?? null;

            if (! $countryId) {
                continue;
            }

            $key = $countryId.'|'.Str::lower($row['name']);

            if (isset($lookup[$key])) {
                $map[$row['id']] = $lookup[$key];
            }
        }

        return $map;
    }

    /**
     * @param  array<int, array{id: int, name: string, state_id: int}>  $rows
     * @param  array<int, int>  $stateMap
     */
    private function importCities(array $rows, array $stateMap, DateTimeInterface $now): void
    {
        $existing = [];

        foreach (DB::table('cities')->select('state_id', 'name')->cursor() as $city) {
            $existing[$city->state_id.'|'.Str::lower($city->name)] = true;
        }

        $inserts = [];

        foreach ($rows as $row) {
            $stateId = $stateMap[$row['state_id']] ?? null;

            if (! $stateId) {
                continue;
            }

            $key = $stateId.'|'.Str::lower($row['name']);

            if (isset($existing[$key])) {
                continue;
            }

            $inserts[] = [
                'name' => $row['name'],
                'state_id' => $stateId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $existing[$key] = true;
        }

        foreach (array_chunk($inserts, 500) as $chunk) {
            DB::table('cities')->insert($chunk);
        }
    }

    /**
     * @param  array<string, bool>  $used
     */
    private function uniqueSlug(string $name, array &$used): string
    {
        $base = Str::slug($name) ?: 'country';
        $slug = $base;
        $suffix = 2;

        while (isset($used[$slug])) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        $used[$slug] = true;

        return $slug;
    }
}
