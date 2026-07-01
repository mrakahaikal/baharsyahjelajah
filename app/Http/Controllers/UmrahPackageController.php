<?php

namespace App\Http\Controllers;

use App\Models\UmrahPackage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UmrahPackageController extends Controller
{
    public function index(Request $request): View
    {
        $packages = UmrahPackage::query()
            ->active()
            ->with(['upcomingDepartures' => fn ($query) => $query->limit(3)])
            ->when($request->string('type')->isNotEmpty(), fn ($query) => $query->byType($request->string('type')->toString()))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('pages.umroh.index', compact('packages'));
    }

    public function show(string $locale, string $umrah): View
    {
        /** @var UmrahPackage $package */
        $package = $this->findByTranslatedSlug(UmrahPackage::class, $umrah);

        $package->load([
            'departures',
            'includes',
            'testimonials',
        ]);

        $relatedPackages = UmrahPackage::query()
            ->active()
            ->whereKeyNot($package->id)
            ->latest()
            ->limit(3)
            ->get();

        return view('pages.umroh.show', compact('package', 'relatedPackages'));
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function findByTranslatedSlug(string $model, string $slug): Model
    {
        return $model::query()
            ->where('slug->'.app()->getLocale(), $slug)
            ->orWhere('slug->id', $slug)
            ->firstOrFail();
    }
}
