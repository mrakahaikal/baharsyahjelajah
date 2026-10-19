<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Models\WhatsappTemplate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('draft_whatsapp_followup')]
#[Title('Draf Pesan WhatsApp Follow-up & Konsultasi')]
#[Description('Panduan AI dalam menyusun draf pesan chat WhatsApp follow-up atau penawaran ramah sesuai template resmi perusahaan.')]
class DraftWhatsappFollowupPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $productType = (string) $request->get('product_type', 'tour');
        $customerName = (string) $request->get('customer_name', 'Bapak/Ibu');
        $productName = (string) $request->get('product_name', 'Paket Wisata Baharsyah');
        $locale = (string) $request->get('locale', 'id');
        $notes = (string) $request->get('notes', '');

        $template = WhatsappTemplate::query()
            ->where('product_type', $productType)
            ->where('locale', $locale)
            ->first();

        $templateText = $template ? $template->template : 'Halo {customer_name}, terima kasih telah menghubungi Baharsyah Jelajah mengenai {product_name}.';

        $instruction = <<<PROMPT
Anda adalah Customer Relationship Specialist & Travel Concierge untuk Baharsyah Jelajah.

Tugas Anda adalah menyusun naskah pesan WhatsApp yang hangat, ramah, persuasif, dan sangat profesional untuk dikirimkan kepada calon pelanggan.

Profil & Data Prospek:
- Nama Pelanggan: "{$customerName}"
- Produk Yang Diminati: "{$productName}" ({$productType})
- Bahasa: "{$locale}"
- Catatan Khusus / Kebutuhan Tambahan: "{$notes}"

Template Standar Sistem:
"{$templateText}"

Instruksi Output:
1. **Naskah Chat WhatsApp Siap Kirim**:
   - Terapkan formatting WhatsApp yang rapi (*bold* untuk kata penting, baris kosong antar paragraf, emoji secukupnya dan elegan).
   - Sapalah pelanggan dengan hormat.
   - Cantumkan poin-poin keuntungan utama yang relevan dengan pertanyaan mereka.
   - Sertakan Call-to-Action (CTA) yang santun (misal: "Apakah jadwal keberangkatan tersebut sudah sesuai dengan rencana cuti Bapak/Ibu?").
2. **Alternatif Versi Singkat**: Berikan 1 versi alternatif yang lebih ringkas jika pelanggan sedang sibuk.
3. **Link WhatsApp**: Berikan URL langsung menggunakan tool `render_whatsapp_message` jika ingin mengirim pesan otomatis.
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
                name: 'customer_name',
                description: 'Nama calon pelanggan atau pemesan.',
                required: false,
            ),
            new Argument(
                name: 'product_type',
                description: 'Jenis produk: "tour", "umrah", "vehicle", atau "visa".',
                required: false,
            ),
            new Argument(
                name: 'product_name',
                description: 'Nama spesifik tur, armada, atau paket umrah yang diminati.',
                required: false,
            ),
            new Argument(
                name: 'notes',
                description: 'Catatan permintaan khusus atau pertanyaan pelanggan.',
                required: false,
            ),
            new Argument(
                name: 'locale',
                description: 'Bahasa pesan ("id", "en", "ms"). Default: "id".',
                required: false,
            ),
        ];
    }
}
