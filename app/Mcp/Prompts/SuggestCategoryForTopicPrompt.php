<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Models\PostCategory;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('suggest_category_for_topic')]
#[Title('Rekomendasi Kategori untuk Topik Artikel')]
#[Description('Panduan prompt bagi AI untuk menganalisis topik artikel baru dan merekomendasikan kategori eksisting yang paling cocok atau merumuskan kategori baru bila diperlukan.')]
class SuggestCategoryForTopicPrompt extends Prompt
{
    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $topic = (string) $request->get('topic', '');
        $keywords = (string) $request->get('keywords', '');

        $categories = PostCategory::query()
            ->orderBy('id')
            ->get();

        $categoryList = $categories->map(function (PostCategory $cat): string {
            $nameId = $cat->getTranslation('name', 'id', false) ?: '-';
            $nameEn = $cat->getTranslation('name', 'en', false) ?: '-';
            $slugId = $cat->getTranslation('slug', 'id', false) ?: '-';

            return "- ID {$cat->id}: \"{$nameId}\" (EN: \"{$nameEn}\", Slug: {$slugId})";
        })->implode("\n");

        $instruction = <<<PROMPT
Anda adalah Editorial & SEO Director untuk blog perjalanan Baharsyah Jelajah.

Seorang penulis ingin membuat artikel baru dengan topik berikut:
- **Topik / Judul**: "{$topic}"
- **Kata Kunci Tambahan**: "{$keywords}"

Berikut adalah daftar kategori artikel yang saat ini tersedia di sistem:
{$categoryList}

### Instruksi Tugas Anda:
1. **Analisis Keselarasan**: Evaluasi apakah topik artikel di atas cocok dimasukkan ke dalam salah satu kategori yang sudah ada.
2. **Rekomendasi Kategori Eksisting**: Jika ada kategori yang cocok, sebutkan ID dan nama kategorinya beserta alasan relevansinya.
3. **Usulan Kategori Baru (Alternatif)**: Jika topik tersebut tidak sesuai dengan kategori mana pun dan mewakili pilar konten baru yang potensial, rumuskan usulan kategori baru lengkap dengan:
   - Nama Bahasa Indonesia ('id')
   - Nama Bahasa Inggris ('en')
   - Nama Bahasa Melayu ('ms')
   - Slug yang disarankan
   - Deskripsi singkat
   - Rekomendasi pemanggilan tool `create_article_category` untuk mengeksekusinya.
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
                description: 'Topik, gagasan, atau rancangan judul artikel yang ingin diklasifikasikan.',
                required: true,
            ),
            new Argument(
                name: 'keywords',
                description: 'Kata kunci pendukung topik artikel (opsional).',
                required: false,
            ),
        ];
    }
}
