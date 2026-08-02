<?php

namespace App\Models;

use App\Helpers\LocaleHelper;
use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['umrah_package_id', 'duration_nights', 'pax', 'price_idr'])]
class UmrahPackagePrivatePrice extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'duration_nights' => 'integer',
            'pax' => 'integer',
            'price_idr' => 'integer',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(UmrahPackage::class, 'umrah_package_id');
    }

    public function getFormattedPriceAttribute(): string
    {
        return app(CurrencyService::class)->convert($this->price_idr, LocaleHelper::currency());
    }

    public function formattedTotal(): string
    {
        return app(CurrencyService::class)->convert($this->price_idr * $this->pax, LocaleHelper::currency());
    }
}
