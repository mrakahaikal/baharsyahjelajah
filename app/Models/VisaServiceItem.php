<?php

namespace App\Models;

use App\Enums\VisaItemType;
use Database\Factories\VisaServiceItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['visa_service_id', 'type', 'content', 'details', 'is_mandatory', 'sort_order'])]
class VisaServiceItem extends Model
{
    /** @use HasFactory<VisaServiceItemFactory> */
    use HasFactory;

    use HasTranslations;

    public array $translatable = ['content', 'details'];

    protected $attributes = [
        'is_mandatory' => true,
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'type' => VisaItemType::class,
            'is_mandatory' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function visaService(): BelongsTo
    {
        return $this->belongsTo(VisaService::class);
    }
}
