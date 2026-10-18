<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Countries;

use App\Models\Country;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_country')]
#[Title('Delete Country')]
#[Description('Safely delete (soft-delete) a country. If connected to destinations, visa services, tours, packages, or articles, requires force=true to confirm detaching.')]
class DeleteCountryTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the country to delete.'),
            'slug' => $schema->string()->description('The slug of the country to delete (used if "id" is omitted).'),
            'iso_code' => $schema->string()->description('The 2-letter or 3-letter ISO code (e.g. "ID", "SA", "EGY"). Used if "id" and "slug" are omitted.'),
            'force' => $schema->boolean()->description('Force deletion by automatically detaching linked destinations, tour packages, umrah packages, articles, and soft-deleting associated visa services. Default is false.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');
        $isoCode = $request->get('iso_code');

        if (empty($id) && empty($slug) && empty($isoCode)) {
            return Response::error('At least one of "id", "slug", or "iso_code" must be provided.');
        }

        /** @var Country|null $country */
        if (! empty($id)) {
            $country = Country::find((int) $id);
        } elseif (! empty($slug)) {
            $country = Country::where('slug', (string) $slug)->first();
        } else {
            $code = Str::upper(trim((string) $isoCode));
            $country = Country::where('iso_alpha_2', $code)
                ->orWhere('iso_alpha_3', $code)
                ->first();
        }

        if (! $country) {
            $identifier = ! empty($id)
                ? "ID {$id}"
                : (! empty($slug) ? "slug '{$slug}'" : "ISO code '{$isoCode}'");

            return Response::error("Country with {$identifier} not found.");
        }

        $force = (bool) $request->get('force', false);

        $destinationsCount = $country->destinations()->count();
        $visaServicesCount = $country->visaServices()->count();
        $tourPackagesCount = $country->tourPackages()->count();
        $umrahPackagesCount = $country->umrahPackages()->count();
        $postsCount = $country->posts()->count();
        $totalLinked = $destinationsCount + $visaServicesCount + $tourPackagesCount + $umrahPackagesCount + $postsCount;

        $name = $country->getTranslation('name', 'id', false);

        if ($totalLinked > 0 && ! $force) {
            return Response::error(
                "Cannot delete country '{$name}' because it is currently linked to {$totalLinked} item(s) ({$destinationsCount} destination(s), {$visaServicesCount} visa service(s), {$tourPackagesCount} tour package(s), {$umrahPackagesCount} umrah package(s), {$postsCount} article(s)). Set 'force' => true to detach links and delete."
            );
        }

        $countryId = $country->id;

        DB::transaction(function () use ($country): void {
            $country->destinations()->detach();
            $country->tourPackages()->detach();
            $country->umrahPackages()->detach();
            $country->vehicles()->detach();
            $country->posts()->detach();
            $country->tours()->detach();
            $country->visaServices()->delete();
            $country->delete();
        });

        return Response::json([
            'message' => "Country '{$name}' (ID {$countryId}) deleted successfully.",
            'deleted_id' => $countryId,
            'detached_links' => [
                'destinations' => $destinationsCount,
                'visa_services' => $visaServicesCount,
                'tour_packages' => $tourPackagesCount,
                'umrah_packages' => $umrahPackagesCount,
                'articles' => $postsCount,
            ],
        ]);
    }
}
