<?php

namespace App\Models;

use Database\Factories\VehicleRentalAreaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug', 'description', 'minimum_rental_days', 'is_active', 'sort_order'])]
class VehicleRentalArea extends Model
{
    /** @use HasFactory<VehicleRentalAreaFactory> */
    use HasFactory, HasTranslations, SoftDeletes;

    public array $translatable = ['name', 'description'];

    protected $attributes = [
        'minimum_rental_days' => 1,
        'is_active' => true,
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'minimum_rental_days' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function rates(): HasMany
    {
        return $this->hasMany(VehicleRentalRate::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
