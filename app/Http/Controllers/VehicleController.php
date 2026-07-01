<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $vehicles = Vehicle::query()
            ->available()
            ->with('galleries')
            ->when($request->integer('pax') > 0, fn ($query) => $query->byCapacity($request->integer('pax')))
            ->orderBy('capacity_pax')
            ->paginate(12)
            ->withQueryString();

        return view('pages.transport.index', compact('vehicles'));
    }

    public function show(string $locale, string $vehicle): View
    {
        /** @var Vehicle $vehicle */
        $vehicle = $this->findByTranslatedSlug(Vehicle::class, $vehicle);

        $vehicle->load(['galleries', 'testimonials']);

        $relatedVehicles = Vehicle::query()
            ->available()
            ->whereKeyNot($vehicle->id)
            ->orderBy('capacity_pax')
            ->limit(4)
            ->get();

        return view('pages.transport.show', compact('vehicle', 'relatedVehicles'));
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
