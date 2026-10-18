<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Destinations;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Country;
use App\Models\Destination;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_destination')]
#[Title('Update Destination')]
#[Description('Update an existing destination: name, slug, description, location, map URL, status, or linked countries.')]
class UpdateDestinationTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the destination to update.'),
            'slug' => $schema->string()->description('The current slug of the destination to update (used if "id" is omitted).'),
            'name' => $schema->string()->description('Updated name. Can be a string or JSON object with keys "id", "en", "ms".'),
            'custom_slug' => $schema->string()->description('Updated URL slug.'),
            'description' => $schema->string()->description('Updated description. Can be a string or JSON object with keys "id", "en", "ms".'),
            'location' => $schema->string()->description('Updated administrative location or city.'),
            'map_url' => $schema->string()->description('Updated Google Maps URL.'),
            'is_active' => $schema->boolean()->description('Updated active status.'),
            'is_featured' => $schema->boolean()->description('Updated featured status.'),
            'country_ids' => $schema->array()->description('Replacement array of Country IDs.')->items($schema->integer()),
            'country_slugs' => $schema->array()->description('Replacement array of Country slugs.')->items($schema->string()),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');

        if (empty($id) && empty($slug)) {
            return Response::error('Either "id" or "slug" must be provided to locate the destination to update.');
        }

        $query = Destination::query()->with('countries');

        /** @var Destination|null $destination */
        $destination = ! empty($id)
            ? $query->find((int) $id)
            : $query->where('slug', (string) $slug)->first();

        if (! $destination) {
            $identifier = ! empty($id) ? "ID {$id}" : "slug '{$slug}'";

            return Response::error("Destination with {$identifier} not found.");
        }

        $attributesToUpdate = [];

        // Name
        if ($request->has('name')) {
            $currentName = $destination->getTranslations('name');
            $newName = $this->mergeTranslations($currentName, $request->get('name'));
            $attributesToUpdate['name'] = $newName;
        }

        // Slug
        if ($request->has('custom_slug')) {
            $rawCustomSlug = (string) $request->get('custom_slug');
            $newSlug = Str::slug($rawCustomSlug);
            if (empty($newSlug)) {
                return Response::error('Custom slug cannot be empty.');
            }

            $slugConflict = Destination::query()
                ->where('slug', $newSlug)
                ->where('id', '!=', $destination->id)
                ->exists();

            if ($slugConflict) {
                return Response::error("A destination with the slug '{$newSlug}' already exists.");
            }

            $attributesToUpdate['slug'] = $newSlug;
        }

        // Description
        if ($request->has('description')) {
            $currentDesc = $destination->getTranslations('description');
            $newDesc = $this->mergeTranslations($currentDesc, $request->get('description'));
            $attributesToUpdate['description'] = $newDesc;
        }

        // Location
        if ($request->has('location')) {
            $attributesToUpdate['location'] = $request->get('location');
        }

        // Map URL
        if ($request->has('map_url')) {
            $mapUrl = $request->get('map_url');
            if (! empty($mapUrl) && filter_var($mapUrl, FILTER_VALIDATE_URL) === false) {
                return Response::error("Invalid map_url format: {$mapUrl}");
            }
            $attributesToUpdate['map_url'] = $mapUrl;
        }

        // is_active
        if ($request->has('is_active')) {
            $attributesToUpdate['is_active'] = (bool) $request->get('is_active');
        }

        // is_featured
        if ($request->has('is_featured')) {
            $attributesToUpdate['is_featured'] = (bool) $request->get('is_featured');
        }

        // Countries
        $hasCountriesUpdate = $request->has('country_ids') || $request->has('country_slugs');
        $resolvedCountryIds = $hasCountriesUpdate
            ? $this->resolveCountryIds($request->get('country_ids'), $request->get('country_slugs'))
            : null;

        DB::transaction(function () use ($destination, $attributesToUpdate, $hasCountriesUpdate, $resolvedCountryIds): void {
            if (! empty($attributesToUpdate)) {
                $destination->update($attributesToUpdate);
            }

            if ($hasCountriesUpdate && is_array($resolvedCountryIds)) {
                $destination->countries()->sync($resolvedCountryIds);
            }
        });

        $destination->refresh()->load('countries');

        return Response::json([
            'message' => 'Destination updated successfully.',
            'destination' => [
                'id' => $destination->id,
                'name' => $destination->getTranslation('name', 'id', false),
                'slug' => $destination->slug,
                'location' => $destination->location,
                'is_active' => $destination->is_active,
                'is_featured' => $destination->is_featured,
                'countries' => $destination->countries->map(fn (Country $c) => [
                    'id' => $c->id,
                    'name' => $c->getTranslation('name', 'id', false),
                    'slug' => $c->slug,
                ])->values()->all(),
                'updated_at' => $destination->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Merge current translations with input.
     *
     * @param  array<string, mixed>  $current
     * @return array<string, string>
     */
    protected function mergeTranslations(array $current, mixed $input): array
    {
        $normalized = $this->normalizeMultilingualInput($input);

        return array_merge($current, array_filter($normalized, fn ($val): bool => $val !== ''));
    }

    /**
     * Resolve country IDs from IDs and slugs.
     *
     * @return array<int>
     */
    protected function resolveCountryIds(mixed $rawIds, mixed $rawSlugs): array
    {
        $ids = [];

        if (is_string($rawIds)) {
            $decoded = json_decode($rawIds, true);
            if (is_array($decoded)) {
                $rawIds = $decoded;
            }
        }
        if (is_array($rawIds)) {
            foreach ($rawIds as $id) {
                if (is_numeric($id)) {
                    $ids[] = (int) $id;
                }
            }
        }

        if (is_string($rawSlugs)) {
            $decoded = json_decode($rawSlugs, true);
            if (is_array($decoded)) {
                $rawSlugs = $decoded;
            }
        }
        if (is_array($rawSlugs) && ! empty($rawSlugs)) {
            $matchedIds = Country::query()
                ->whereIn('slug', $rawSlugs)
                ->pluck('id')
                ->all();

            $ids = array_merge($ids, $matchedIds);
        }

        return array_values(array_unique($ids));
    }
}

