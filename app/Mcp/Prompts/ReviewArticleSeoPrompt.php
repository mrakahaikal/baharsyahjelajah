<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Models\Post;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('review_article_seo')]
#[Title('Audit SEO & Kualitas Konten Artikel')]
#[Description('Panduan audit mendalam terhadap kualitas artikel blog: struktur heading semantik, kepatuhan RichText HTML, kelengkapan multilingual, dan optimasi SEO.')]
class ReviewArticleSeoPrompt extends Prompt
{
    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $id = $request->get('article_id');
        $slug = $request->get('slug');

        $post = null;
        if (! empty($id)) {
            $post = Post::with(['category', 'destinations', 'author'])->find((int) $id);
        } elseif (! empty($slug)) {
            $post = Post::with(['category', 'destinations', 'author'])->whereLocalizedSlug((string) $slug)->first();
        }

        $articleContext = '';
        if ($post) {
            $destinationsList = $post->destinations->pluck('name')->map(fn ($n) => is_array($n) ? ($n['id'] ?? '') : $n)->filter()->implode(', ') ?: 'Belum ada';
            $categoryName = $post->category ? $post->category->getTranslation('name', 'id', false) : 'Tanpa Kategori';

            $articleContext = <<<INFO
### Informasi Artikel yang Diaudit:
- **ID**: {$post->id}
- **Judul (ID)**: {$post->getTranslation('title', 'id', false)}
- **Judul (EN)**: {$post->getTranslation('title', 'en', false)}
- **Judul (MS)**: {$post->getTranslation('title', 'ms', false)}
- **Slug (ID)**: {$post->localizedSlug('id')}
- **Kategori**: {$categoryName}
- **Status Publikasi**: {$post->status}
- **Destinasi Terkait**: {$destinationsList}
- **Ringkasan (Excerpt ID)**: {$post->getTranslation('excerpt', 'id', false)}

#### Cuplikan Isi Konten (ID):
```html
{$post->getTranslation('content', 'id', false)}
```
INFO;
        } else {
            $articleContext = 'Catatan: Artikel spesifik belum dipilih atau tidak ditemukan. Berikan panduan audit SEO umum berdasarkan ID artikel yang nanti dimasukkan.';
        }

        $instruction = <<<PROMPT
Anda adalah Senior SEO Specialist & Content Quality Auditor untuk portal blog wisata Baharsyah Jelajah.

{$articleContext}

### Tugas & Aspek Pemeriksaan Wajib:
1. **Audit Format RichText HTML**:
   - Periksa apakah isi konten sudah tersusun dalam tag RichText HTML semantik (`<p>`, `<h2>`, `<h3>`, `<ul>`, `<li>`, `<blockquote>`, `<strong>`).
   - Pastikan tidak ada paragraf yang berupa teks polos tanpa pembungkus `<p>`.
2. **Audit Keterbacaan & On-Page SEO**:
   - Evaluasi panjang paragraf (maksimal 3-4 baris per paragraf `<p>` untuk kenyamanan membaca di perangkat seluler).
   - Pastikan struktur heading berjenjang (`<h2>` untuk poin utama, `<h3>` untuk sub-poin).
   - Evaluasi apakah kata kunci destinasi/wisata sudah ditempatkan secara natural di pembuka, subjudul, dan isi.
3. **Audit Kualitas Multilingual**:
   - Periksa apakah judul, ringkasan (`excerpt`), dan isi konten bahasa Inggris (`en`) dan bahasa Melayu (`ms`) sudah lengkap dan berkualitas profesional, bukan sekadar salinan kasar.
4. **Audit Keterkaitan Destinasi**:
   - Jika artikel membahas lokasi wisata tertentu namun belum terhubung ke destinasi sistem, rekomendasikan destinasi yang harus disambungkan via `destination_slugs`.
5. **Rekomendasi Pembaruan Konkrit**:
   - Sajikan perbaikan teks HTML secara lengkap.
   - Berikan panduan pemanggilan tool `update_article` untuk memperbarui artikel secara langsung di sistem.
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
                name: 'article_id',
                description: 'ID numerik artikel yang ingin diaudit (opsional jika slug diberikan).',
                required: false,
            ),
            new Argument(
                name: 'slug',
                description: 'Slug URL artikel yang ingin diaudit (opsional jika article_id diberikan).',
                required: false,
            ),
        ];
    }
}
