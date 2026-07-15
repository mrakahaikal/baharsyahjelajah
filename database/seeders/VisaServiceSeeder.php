<?php

namespace Database\Seeders;

use App\Enums\VisaEntryType;
use App\Enums\VisaItemType;
use App\Models\Country;
use App\Models\VisaService;
use Illuminate\Database\Seeder;

class VisaServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            $this->egyptService(),
            $this->saudiArabiaService(),
        ];

        foreach ($services as $serviceData) {
            $country = Country::query()->where('iso_alpha_2', $serviceData['country_code'])->firstOrFail();
            $service = VisaService::query()->updateOrCreate(
                ['slug' => $serviceData['service']['slug']],
                [...$serviceData['service'], 'country_id' => $country->getKey()],
            );

            foreach ($serviceData['items'] as $sortOrder => $item) {
                $service->items()->updateOrCreate(
                    ['type' => $item['type']->value, 'sort_order' => $sortOrder + 1],
                    [
                        'content' => $item['content'],
                        'details' => null,
                        'is_mandatory' => $item['is_mandatory'],
                    ],
                );
            }
        }
    }

    /** @return array<string, mixed> */
    private function egyptService(): array
    {
        return [
            'country_code' => 'EG',
            'service' => $this->serviceAttributes(
                name: ['id' => 'Layanan Visa Mesir', 'en' => 'Egypt Visa Service', 'ms' => 'Perkhidmatan Visa Mesir'],
                slug: 'layanan-visa-mesir',
                summary: [
                    'id' => 'Pendampingan pengurusan visa Mesir untuk pemegang paspor Indonesia.',
                    'en' => 'Egypt visa assistance for Indonesian passport holders.',
                    'ms' => 'Bantuan visa Mesir untuk pemegang pasport Indonesia.',
                ],
                processingDaysMin: 7,
                processingDaysMax: 14,
                sortOrder: 1,
            ),
            'items' => [
                $this->item('Paspor Indonesia dengan masa berlaku minimal 6 bulan', 'Indonesian passport valid for at least 6 months', 'Pasport Indonesia dengan tempoh sah sekurang-kurangnya 6 bulan'),
                $this->item('Pas foto terbaru dengan latar belakang putih', 'Recent passport photo with a white background', 'Foto pasport terkini dengan latar belakang putih'),
                $this->item('Waktu proses dihitung setelah dokumen dinyatakan lengkap', 'Processing starts after all documents are complete', 'Proses bermula selepas semua dokumen lengkap', VisaItemType::Term, false),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function saudiArabiaService(): array
    {
        return [
            'country_code' => 'SA',
            'service' => $this->serviceAttributes(
                name: ['id' => 'Layanan Visa Arab Saudi', 'en' => 'Saudi Arabia Visa Service', 'ms' => 'Perkhidmatan Visa Arab Saudi'],
                slug: 'layanan-visa-arab-saudi',
                summary: [
                    'id' => 'Pendampingan pengurusan visa Arab Saudi untuk pemegang paspor Indonesia.',
                    'en' => 'Saudi Arabia visa assistance for Indonesian passport holders.',
                    'ms' => 'Bantuan visa Arab Saudi untuk pemegang pasport Indonesia.',
                ],
                processingDaysMin: 5,
                processingDaysMax: 10,
                sortOrder: 2,
            ),
            'items' => [
                $this->item('Paspor Indonesia dengan masa berlaku minimal 6 bulan', 'Indonesian passport valid for at least 6 months', 'Pasport Indonesia dengan tempoh sah sekurang-kurangnya 6 bulan'),
                $this->item('Kartu Tanda Penduduk dan Kartu Keluarga', 'Indonesian identity card and family card', 'Kad pengenalan Indonesia dan kad keluarga'),
                $this->item('Persetujuan visa tetap menjadi kewenangan imigrasi Arab Saudi', 'Visa approval remains subject to Saudi immigration authority', 'Kelulusan visa tertakluk kepada pihak imigresen Arab Saudi', VisaItemType::Term, false),
            ],
        ];
    }

    /**
     * @param  array{id: string, en: string, ms: string}  $name
     * @param  array{id: string, en: string, ms: string}  $summary
     * @return array<string, mixed>
     */
    private function serviceAttributes(array $name, string $slug, array $summary, int $processingDaysMin, int $processingDaysMax, int $sortOrder): array
    {
        return [
            'name' => $name,
            'slug' => $slug,
            'visa_type' => ['id' => 'Visa Kunjungan', 'en' => 'Visit Visa', 'ms' => 'Visa Lawatan'],
            'summary' => $summary,
            'description' => [
                'id' => '<p>Layanan pendampingan dokumen dan proses pengajuan visa untuk warga negara Indonesia.</p>',
                'en' => '<p>Document preparation and visa application assistance for Indonesian citizens.</p>',
                'ms' => '<p>Bantuan penyediaan dokumen dan permohonan visa untuk warga Indonesia.</p>',
            ],
            'entry_type' => VisaEntryType::Single,
            'processing_days_min' => $processingDaysMin,
            'processing_days_max' => $processingDaysMax,
            'validity_days' => 90,
            'maximum_stay_days' => 30,
            'price_idr' => null,
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => $sortOrder,
        ];
    }

    /** @return array<string, mixed> */
    private function item(string $id, string $en, string $ms, VisaItemType $type = VisaItemType::Requirement, bool $isMandatory = true): array
    {
        return [
            'type' => $type,
            'content' => compact('id', 'en', 'ms'),
            'is_mandatory' => $isMandatory,
        ];
    }
}
