<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Models\Destination;
use App\Models\PostCategory;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('draft_article')]
#[Title('Draf Artikel Wisata Multilingual')]
#[Description('Panduan prompt terstruktur bagi AI untuk menyusun artikel blog perjalanan baru dalam format RichText HTML semantik, translasi (id, en, ms), dan keterkaitan destinasi.')]
class DraftArticlePrompt extends Prompt
{
    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $topic = (string) $request->get('topic', '');
        $destinationInput = (string) $request->get('target_destination', '');
        $categorySlug = (string) $request->get('category_slug', '');
        $tone = (string) $request->get('tone', 'inspiratif, petualang, dan informatif');

        // Look up categories
        $categories = PostCategory::query()->orderBy('id')->get()->map(function (PostCategory $cat): string {
            $name = $cat->getTranslation('name', 'id', false);
            $slug = $cat->localizedSlug('id');

            return "- {$name} (slug: {$slug}, id: {$cat->id})";
        })->implode("\n");

        // Look up active destinations
        $destinations = Destination::query()->active()->get()->map(function (Destination $d): string {
            $name = $d->getTranslation('name', 'id', false);

            return "- {$name} (slug: {$d->slug}, id: {$d->id})";
        })->implode("\n");

        $instruction = <<<PROMPT
Anda adalah Senior Travel Writer & SEO Content Specialist untuk Baharsyah Jelajah (biro perjalanan wisata dan tur Kalimantan & Nusantara).

Tugas Anda adalah merancang draf artikel blog perjalanan yang menarik berdasarkan parameter berikut:
- **Topik / Judul Gagasan**: "{$topic}"
- **Destinasi Sorotan**: "{$destinationInput}"
- **Kategori Pilihan**: "{$categorySlug}"
- **Tone & Gaya Bahasa**: "{$tone}"

### Kategori yang Tersedia:
{$categories}

### Destinasi yang Terdaftar:
{$destinations}

### Panduan Penulisan & Format Wajib:
1. **Format Konten Wajib RichText HTML**:
   - Seluruh isi konten (`content`) **wajib ditulis dalam tag RichText HTML semantik**:
     - Gunakan tag paragraf `<p>` untuk setiap paragraf (jangan gunakan teks polos biasa tanpa tag).
     - Gunakan subjudul `<h2>` dan `<h3>` untuk struktur artikel yang rapi.
     - Gunakan daftar bertingkat `<ul>` dan `<li>` untuk tips, perlengkapan, atau rute.
     - Gunakan penekanan `<strong>` dan `<em>` secara wajar.
     - Tambahkan kutipan atau highlight tips menggunakan `<blockquote>`.
2. **Kelengkapan Multilingual**:
   - Susun judul (`title`), slug URL, ringkasan (`excerpt`), dan isi konten (`content`) dalam 3 bahasa:
     - **Bahasa Indonesia (`id`)**: Bahasa utama yang lugas, mengalir, dan menggugah minat berwisata.
     - **Bahasa Inggris (`en`)**: Bahasa internasional yang menarik bagi wisatawan mancanegara.
     - **Bahasa Melayu (`ms`)**: Bahasa yang akrab dan sesuai bagi pelancong regional (Malaysia/Brunei).
3. **Keterkaitan Destinasi**:
   - Hubungkan artikel dengan destinasi terkait menggunakan parameter `destination_slugs` atau `destination_ids`.
4. **Eksekusi Pembuatan**:
   - Tampilkan draf final, lalu panggil MCP tool `create_article` dengan payload terstruktur:
   ```json
   {
     "title": { "id": "...", "en": "...", "ms": "..." },
     "slug": { "id": "...", "en": "...", "ms": "..." },
     "excerpt": { "id": "...", "en": "...", "ms": "..." },
     "content": { "id": "<p>...</p>", "en": "<p>...</p>", "ms": "<p>...</p>" },
     "category_slug": "...",
     "destination_slugs": ["..."],
     "status": "draft"
   }
   ```
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
                name: 'topic',
                description: 'Topik, gagasan ide, atau judul artikel yang ingin ditulis.',
                required: true,
            ),
            new Argument(
                name: 'target_destination',
                description: 'Nama destinasi wisata yang ingin disorot (misal: "Derawan", "Kakaban", "Maratua", "Kutai").',
                required: false,
            ),
            new Argument(
                name: 'category_slug',
                description: 'Slug kategori artikel yang dituju (opsional).',
                required: false,
            ),
            new Argument(
                name: 'tone',
                description: 'Tone atau gaya penulisan (default: "inspiratif, petualang, dan informatif").',
                required: false,
            ),
        ];
    }
}
