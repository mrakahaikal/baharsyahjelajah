<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Models\Destination;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('plan_destination_highlight')]
#[Title('Rencana Sorotan Destinasi & Itinerary')]
#[Description('Panduan prompt terstruktur bagi AI untuk merancang program sorotan destinasi: daya tarik utama, rekomendasi itinerary, persiapan wisata, dan artikel blog pendukung.')]
class PlanDestinationHighlightPrompt extends Prompt
{
    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $id = $request->get('destination_id');
        $slug = $request->get('destination_slug');
        $duration = (int) $request->get('duration_days', 3);
        $duration = max(1, min(14, $duration));
        $audience = (string) $request->get('target_audience', 'wisatawan keluarga dan petualang');

        $destination = null;
        if (! empty($id)) {
            $destination = Destination::with(['countries', 'posts', 'tourPackages'])->find((int) $id);
        } elseif (! empty($slug)) {
            $destination = Destination::with(['countries', 'posts', 'tourPackages'])->where('slug', (string) $slug)->first();
        }

        $destContext = '';
        if ($destination) {
            $nameId = $destination->getTranslation('name', 'id', false);
            $nameEn = $destination->getTranslation('name', 'en', false);
            $descId = $destination->getTranslation('description', 'id', false);
            $countries = $destination->countries->pluck('name')->map(fn ($n) => is_array($n) ? ($n['id'] ?? '') : $n)->filter()->implode(', ') ?: 'Indonesia';
            $postsCount = $destination->posts->count();
            $toursCount = $destination->tourPackages->count();
            $statusLabel = $destination->is_active ? 'Aktif' : 'Nonaktif';
            $featuredLabel = $destination->is_featured ? 'Ya' : 'Tidak';

            $destContext = <<<INFO
### Profil Destinasi yang Disorot:
- **Nama**: {$nameId} (EN: {$nameEn})
- **Slug**: {$destination->slug}
- **Lokasi**: {$destination->location}
- **Negara**: {$countries}
- **Peta Google Maps**: {$destination->map_url}
- **Status**: {$statusLabel} (Unggulan: {$featuredLabel})
- **Deskripsi Saat Ini**: {$descId}
- **Konten Terhubung**: {$postsCount} artikel blog, {$toursCount} paket tur wisata.
INFO;
        } else {
            $destContext = 'Catatan: Destinasi spesifik belum dipilih. Gunakan daftar destinasi aktif dari resource "destinations://catalog" untuk memilih destinasi yang ingin disorot.';
        }

        $instruction = <<<PROMPT
Anda adalah Senior Travel Planner & Creative Director untuk Baharsyah Jelajah (spesialis tur & travel Kalimantan Timur dan Nusantara).

{$destContext}

### Parameter Rencana Perjalanan:
- **Durasi Wisata**: {$duration} Hari
- **Target Audiens**: {$audience}

### Tugas Perancangan yang Wajib Disusun:
1. **Narasi Daya Tarik Utama (*Unique Selling Points*)**:
   - Uraikan keunikan alam, budaya, kuliner, dan pengalaman otentik yang tidak ditemukan di tempat lain.
2. **Rancangan Itinerary Harian ({$duration} Hari)**:
   - Buat jadwal harian terstruktur: Waktu (Pagi/Siang/Sore/Malam), nama spot wisata, aktivitas menarik, dan rekomendasi kuliner lokal.
3. **Tips Logistik & Perlengkapan**:
   - Transportasi terbaik (jalur darat, sungai, atau udara).
   - Waktu terbaik berkunjung (*best season*).
   - Perlengkapan wajib bagi wisatawan.
4. **Strategi Konten & Call to Action**:
   - Rekomendasikan topik artikel blog pendukung yang dapat dibuat dengan tool `create_article` dan dihubungkan ke destinasi ini (`destination_slugs`).
PROMPT;

        return Response::text($instruction);
    }

    /**
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument(
                name: 'destination_slug',
                description: 'Slug URL destinasi yang ingin disorot (misal: "kepulauan-derawan", "taman-nasional-kutai").',
                required: false,
            ),
            new Argument(
                name: 'destination_id',
                description: 'ID numerik destinasi (opsional jika slug diberikan).',
                required: false,
            ),
            new Argument(
                name: 'duration_days',
                description: 'Durasi liburan dalam jumlah hari (default: 3).',
                required: false,
            ),
            new Argument(
                name: 'target_audience',
                description: 'Target audiens wisata (contoh: "wisatawan keluarga", "backpacker petualang", "wisatawan mancanegara").',
                required: false,
            ),
        ];
    }
}
