<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Tours\TourResource;
use App\Filament\Resources\UmrahPackages\UmrahPackageResource;
use App\Filament\Resources\Vehicles\VehicleResource;
use App\Models\Post;
use App\Models\Tour;
use App\Models\UmrahPackage;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $activeTours = Tour::where('is_active', true)->count();
        $totalTours = Tour::count();

        $activeUmrah = UmrahPackage::where('is_active', true)->count();
        $totalUmrah = UmrahPackage::count();

        $activeVehicles = Vehicle::where('is_active', true)->count();
        $totalVehicles = Vehicle::count();

        $publishedPosts = Post::where('status', 'published')->count();
        $totalPosts = Post::count();

        return [
            Stat::make('Paket Tur', "{$activeTours} / {$totalTours}")
                ->description("{$activeTours} paket aktif siap dipesan")
                ->descriptionIcon('lucide-compass')
                ->color('success')
                ->url(TourResource::getUrl('index')),

            Stat::make('Paket Umrah', "{$activeUmrah} / {$totalUmrah}")
                ->description("{$activeUmrah} paket umrah aktif")
                ->descriptionIcon('lucide-landmark')
                ->color('warning')
                ->url(UmrahPackageResource::getUrl('index')),

            Stat::make('Armada Rental', "{$activeVehicles} / {$totalVehicles}")
                ->description("{$activeVehicles} unit armada tersedia")
                ->descriptionIcon('lucide-car')
                ->color('info')
                ->url(VehicleResource::getUrl('index')),

            Stat::make('Artikel Blog', "{$publishedPosts} / {$totalPosts}")
                ->description("{$publishedPosts} artikel terpublikasi")
                ->descriptionIcon('lucide-newspaper')
                ->color('primary')
                ->url(PostResource::getUrl('index')),
        ];
    }
}

