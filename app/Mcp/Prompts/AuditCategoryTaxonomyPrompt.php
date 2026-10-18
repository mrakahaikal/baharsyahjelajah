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

#[Name('audit_category_taxonomy')]
#[Title('Audit Kualitas Taksonomi Kategori')]
#[Description('Panduan prompt bagi AI untuk mengaudit kualitas, kelengkapan terjemahan (id, en, ms), dan kebersihan struktur kategori artikel blog.')]
class AuditCategoryTaxonomyPrompt extends Prompt
{
    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $categories = PostCategory::query()
            ->withCount('posts')
            ->orderBy('id')
            ->get();

        $categoryList = $categories->map(function (PostCategory $cat): string {
            $nameId = $cat->getTranslation('name', 'id', false) ?: '-';
            $nameEn = $cat->getTranslation('name', 'en', false) ?: '-';
            $nameMs = $cat->getTranslation('name', 'ms', false) ?: '-';
            $slugId = $cat->getTranslation('slug', 'id', false) ?: '-';

            return "- ID {$cat->id}: \"{$nameId}\" (EN: \"{$nameEn}\", MS: \"{$nameMs}\") [Slug ID: {$slugId}] | {$cat->posts_count} artikel";
        })->implode("\n");

        $instruction = <<<PROMPT
Anda adalah Senior Content Strategist dan Data Quality Specialist untuk blog perjalanan & tur wisata Baharsyah Jelajah.

Tugas Anda adalah mengaudit struktur taksonomi kategori artikel saat ini dan memberikan rekomendasi optimasi konkrit:

### Data Kategori Saat Ini:
{$categoryList}

### Aspek yang Wajib Diaudit:
1. **Kategori Kosong**: Identifikasi kategori yang memiliki 0 artikel. Berikan rekomendasi apakah perlu dibuatkan artikel baru, digabung dengan kategori lain, atau dihapus dengan aman.
2. **Kelengkapan Multilingual**: Periksa apakah ada kategori yang terjemahan bahasa Inggris ('en') atau bahasa Melayu ('ms') bernilai kosong, tidak akurat, atau hanya duplikasi kasar dari bahasa Indonesia ('id').
3. **Kesesuaian Naming & Slug**: Periksa konsistensi penamaan dan slug URL agar ramah SEO dan mudah dipahami wisatawan.
4. **Rencana Aksi Konkrit**: Berikan daftar aksi spesifik yang dapat dieksekusi menggunakan tool MCP (`update_article_category`, `delete_article_category`, dsb.).
PROMPT;

        return Response::text($instruction);
    }

    /**
     * @return array<int, mixed>
     */
    public function arguments(): array
    {
        return [];
    }
}
