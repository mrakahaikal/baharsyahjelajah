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

#[Name('create_destination')]
#[Title('Create Destination')]
#[Description('Create a new travel destination with multilingual name, description, location, map URL, status, and country associations.')]
class CreateDestinationTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Destination name. Can be a string (e.g. "Taman Nasional Tanjung Puting") or a JSON string / object with keys "id", "en", "ms".')->required(),
            'slug' => $schema->string()->description('Optional custom URL slug. If omitted, generated automatically from Indonesian name.'),
            'description' => $schema->string()->description('Destination description or attractions. Can be a string or JSON string / object with keys "id", "en", "ms".'),
            'location' => $schema->string()->description('Administrative location or city (e.g. "Kotawaringin Barat, Kalimantan Tengah").'),
            'map_url' => $schema->string()->description('Google Maps link URL.'),
            'is_active' => $schema->boolean()->description('Whether the destination is publicly active (default: true).'),
            'is_featured' => $schema->boolean()->description('Whether the destination is featured on home/promotions (default: false).'),
            'country_ids' => $schema->array()->description('Array of Country IDs to link this destination with.')->items($schema->integer()),
            'country_slugs' => $schema->array()->description('Array of Country slugs to link this destination with (e.g. ["indonesia"]).')->items($schema->string()),
        ];
    }

    public function handle(Request $request): Response
    {
        $rawName = $request->get('name');
        if (empty($rawName)) {
            return Response::error('The "name" parameter is required.');
        }

        $name = $this->normalizeMultilingualInput($rawName);
        if (empty($name['id'])) {
            return Response::error('Destination name for Indonesian (id) cannot be empty.');
        }

        // Resolve slug
        $rawSlug = $request->get('slug');
        $slug = ! empty($rawSlug) ? Str::slug((string) $rawSlug) : Str::slug($name['id']);

        if (empty($slug)) {
            $slug = 'destination-'.Str::random(6);
        }

        // Check slug uniqueness
        $slugExists = Destination::query()->where('slug', $slug)->exists();
        if ($slugExists) {
            if (! empty($rawSlug)) {
                return Response::error("A destination with the slug '{$slug}' already exists.");
            }
            $slug .= '-'.Str::random(4);
        }

        // Description
        $rawDescription = $request->get('description');
        $description = ! empty($rawDescription) ? $this->normalizeMultilingualInput($rawDescription) : null;

        // Map URL validation
        $mapUrl = $request->get('map_url');
        if (! empty($mapUrl) && filter_var($mapUrl, FILTER_VALIDATE_URL) === false) {
            return Response::error("Invalid map_url format: {$mapUrl}");
        }

        $location = $request->get('location');
        $isActive = (bool) $request->get('is_active', true);
        $isFeatured = (bool) $request->get('is_featured', false);

        // Resolve countries
        $countryIds = $this->resolveCountryIds(
            $request->get('country_ids'),
            $request->get('country_slugs')
        );

        /** @var Destination $destination */
        $destination = DB::transaction(function () use (
            $name,
            $slug,
            $description,
            $location,
            $mapUrl,
            $isActive,
            $isFeatured,
            $countryIds
        ): Destination {
            $dest = Destination::create([
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'location' => $location,
                'map_url' => $mapUrl,
                'is_active' => $isActive,
                'is_featured' => $isFeatured,
            ]);

            if (! empty($countryIds)) {
                $dest->countries()->sync($countryIds);
            }

            return $dest;
        });

        $destination->load('countries');

        return Response::json([
            'message' => 'Destination created successfully.',
            'destination' => [
                'id' => $destination->id,
                'name' => $destination->getTranslation('name', 'id', false),
                'slug' => $destination->slug,
                'location' => $destination->location,
                'map_url' => $destination->getSafeMapUrlAttribute(),
                'is_active' => $destination->is_active,
                'is_featured' => $destination->is_featured,
                'countries' => $destination->countries->map(fn (Country $c) => [
                    'id' => $c->id,
                    'name' => $c->getTranslation('name', 'id', false),
                    'slug' => $c->slug,
                ])->values()->all(),
                'translations' => [
                    'name' => $destination->getTranslations('name'),
                    'description' => $destination->getTranslations('description'),
                ],
                'created_at' => $destination->created_at?->toIso8601String(),
            ],
        ]);
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

