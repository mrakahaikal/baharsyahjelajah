<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['tour_id', 'day_number', 'title', 'description', 'meals_included', 'accommodation'])]
class TourItinerary extends Model
{
    use HasTranslations;

    public array $translatable = ['title', 'description', 'meals_included'];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'day_number'     => 'integer',
        'meals_included' => 'array',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Label ikon untuk setiap meal. Berguna di Blade.
     * Contoh: ['breakfast' => '🌅 Sarapan', 'lunch' => '☀️ Makan Siang']
     */
    public function getMealLabelsAttribute(): array
    {
        if (empty($this->meals_included)) {
            return [];
        }

        $locale = app()->getLocale();

        $labels = [
            'id' => ['breakfast' => '🌅 Sarapan',    'lunch' => '☀️ Makan Siang', 'dinner' => '🌙 Makan Malam'],
            'ms' => ['breakfast' => '🌅 Sarapan Pagi','lunch' => '☀️ Makan Tengah', 'dinner' => '🌙 Makan Malam'],
            'en' => ['breakfast' => '🌅 Breakfast',   'lunch' => '☀️ Lunch',        'dinner' => '🌙 Dinner'],
        ];

        $map = $labels[$locale] ?? $labels['id'];

        return array_map(fn ($meal) => $map[$meal] ?? $meal, $this->meals_included);
    }
}
