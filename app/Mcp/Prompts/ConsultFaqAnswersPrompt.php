<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Enums\FaqCategory;
use App\Models\Faq;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('consult_faq_answers')]
#[Title('Konsultasi Jawaban FAQ Resmi Pelanggan')]
#[Description('Panduan AI dalam mencocokkan pertanyaan atau keluhan pelanggan dengan jawaban resmi FAQ perusahaan (Tur, Umrah, Transport, Pembayaran, Umum).')]
class ConsultFaqAnswersPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $rawCategory = $request->get('category');
        $customerQuery = (string) $request->get('customer_query', '');
        $locale = (string) $request->get('locale', 'id');

        $query = Faq::query()->active()->ordered();

        if (! empty($rawCategory)) {
            $cat = FaqCategory::tryFrom((string) $rawCategory);
            if ($cat) {
                $query->where('category', $cat);
            }
        }

        $faqs = $query->get();

        $faqList = $faqs->map(function (Faq $f) use ($locale): string {
            $cat = $f->category?->getLabel() ?: 'Umum';
            $q = $f->getTranslation('question', $locale, false) ?: $f->getTranslation('question', 'id', false);
            $a = $f->getTranslation('answer', $locale, false) ?: $f->getTranslation('answer', 'id', false);

            return "### [{$cat}] ID {$f->id}: {$q}\n**Jawaban Resmi**: {$a}\n";
        })->implode("\n");

        if (empty($faqList)) {
            $faqList = 'Belum ada data FAQ aktif untuk kategori ini.';
        }

        $systemInstruction = <<<TEXT
Anda adalah Customer Service Assistant & Travel Consultant profesional untuk Baharsyah Jelajah.

Tugas Anda adalah menjawab pertanyaan pelanggan secara ramah, sopan, informatif, dan konsisten dengan kebijakan resmi perusahaan.

Pertanyaan Pelanggan:
"{$customerQuery}"

Basis Pengetahuan FAQ Resmi Perusahaan:
{$faqList}

Instruksi Penanganan:
1. Cocokkan pertanyaan pelanggan dengan butir FAQ di atas yang paling relevan.
2. Sampaikan jawaban secara natural, hangat, dan santun (bukan sekadar copy-paste kaku), tetapi pertahankan seluruh fakta dan ketentuan resmi.
3. Jika pertanyaan pelanggan belum terjawab oleh FAQ yang ada, berikan jawaban tentatif yang aman dan tawarkan bantuan lanjutan melalui tim representatif via WhatsApp.
4. Jika ada kebijakan penting terkait pembayaran, refund, visa, atau durasi sewa, tekankan butir tersebut dengan jelas.
TEXT;

        return Response::text($systemInstruction);
    }

    /**
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument(
                name: 'customer_query',
                description: 'Pertanyaan atau kendala yang diajukan oleh pelanggan/traveler.',
                required: false,
            ),
            new Argument(
                name: 'category',
                description: 'Kategori FAQ: "general", "tour", "umrah", "vehicle", atau "payment".',
                required: false,
            ),
            new Argument(
                name: 'locale',
                description: 'Bahasa preferensi jawaban ("id", "en", "ms"). Default: "id".',
                required: false,
            ),
        ];
    }
}
