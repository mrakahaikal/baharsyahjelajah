<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('footer.brand_description', [
            'id' => 'Baharsyah Jelajah membantu merencanakan tour halal, perjalanan ibadah, dan kebutuhan mobilitas dengan komunikasi yang jelas sejak konsultasi pertama.',
            'ms' => 'Baharsyah Jelajah membantu merancang pelancongan halal, perjalanan ibadah, dan keperluan mobiliti dengan komunikasi yang jelas sejak konsultasi pertama.',
            'en' => 'Baharsyah Jelajah helps plan halal tours, pilgrimage journeys, and mobility needs with clear communication from the first consultation.',
        ]);

        $this->migrator->add('footer.cta_title', [
            'id' => 'Butuh bantuan memilih perjalanan?',
            'ms' => 'Perlukan bantuan memilih perjalanan?',
            'en' => 'Need help choosing a trip?',
        ]);

        $this->migrator->add('footer.cta_subtitle', [
            'id' => 'Ceritakan kebutuhan Anda, tim kami akan membantu menyusun pilihan paket dan rute yang paling sesuai.',
            'ms' => 'Kongsikan keperluan anda, pasukan kami akan membantu menyusun pilihan pakej dan laluan yang paling sesuai.',
            'en' => 'Tell us what you need, and our team will help shortlist the most suitable packages and routes.',
        ]);

        $this->migrator->add('footer.cta_button_label', [
            'id' => 'Hubungi Kami',
            'ms' => 'Hubungi Kami',
            'en' => 'Contact Us',
        ]);

        $this->migrator->add('footer.cta_button_route', 'contact.index');

        $this->migrator->add('footer.link_groups', [
            [
                'title' => [
                    'id' => 'Layanan',
                    'ms' => 'Perkhidmatan',
                    'en' => 'Services',
                ],
                'links' => [
                    [
                        'label' => ['id' => 'Paket Tour', 'ms' => 'Pakej Pelancongan', 'en' => 'Tour Packages'],
                        'route' => 'tour.index',
                        'url' => null,
                    ],
                    [
                        'label' => ['id' => 'Umroh', 'ms' => 'Umrah', 'en' => 'Umrah'],
                        'route' => 'umroh.index',
                        'url' => null,
                    ],
                    [
                        'label' => ['id' => 'Transportasi', 'ms' => 'Pengangkutan', 'en' => 'Transport'],
                        'route' => 'transport.index',
                        'url' => null,
                    ],
                    [
                        'label' => ['id' => 'Visa', 'ms' => 'Visa', 'en' => 'Visa'],
                        'route' => 'visa.index',
                        'url' => null,
                    ],
                ],
            ],
            [
                'title' => [
                    'id' => 'Perusahaan',
                    'ms' => 'Syarikat',
                    'en' => 'Company',
                ],
                'links' => [
                    [
                        'label' => ['id' => 'Blog', 'ms' => 'Blog', 'en' => 'Blog'],
                        'route' => 'blog.index',
                        'url' => null,
                    ],
                    [
                        'label' => ['id' => 'Galeri', 'ms' => 'Galeri', 'en' => 'Gallery'],
                        'route' => 'gallery.index',
                        'url' => null,
                    ],
                    [
                        'label' => ['id' => 'Testimoni', 'ms' => 'Testimoni', 'en' => 'Testimonials'],
                        'route' => 'testimonials.index',
                        'url' => null,
                    ],
                    [
                        'label' => ['id' => 'Kontak', 'ms' => 'Kontak', 'en' => 'Contact'],
                        'route' => 'contact.index',
                        'url' => null,
                    ],
                ],
            ],
        ]);

        $this->migrator->add('footer.legal_links', [
            [
                'label' => ['id' => 'Kontak', 'ms' => 'Kontak', 'en' => 'Contact'],
                'route' => 'contact.index',
                'url' => null,
            ],
            [
                'label' => ['id' => 'FAQ', 'ms' => 'FAQ', 'en' => 'FAQ'],
                'route' => 'home',
                'url' => null,
                'fragment' => 'faq',
            ],
        ]);

    }
};
