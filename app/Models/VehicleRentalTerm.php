<?php

namespace App\Models;

use App\Enums\VehicleCategory;
use App\Enums\VehicleRentalTermType;
use Database\Factories\VehicleRentalTermFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

#[Fillable(['code', 'type', 'vehicle_category', 'title', 'content', 'is_active', 'sort_order'])]
class VehicleRentalTerm extends Model
{
    /** @use HasFactory<VehicleRentalTermFactory> */
    use HasFactory, HasTranslations;

    public array $translatable = ['title', 'content'];

    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'type' => VehicleRentalTermType::class,
            'vehicle_category' => VehicleCategory::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
