<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->rename('footer.subscribe_title', 'footer.social_title');
        $this->migrator->rename('footer.subscribe_subtitle', 'footer.social_description');

        $this->migrator->update('footer.social_title', fn (): array => [
            'id' => 'Ikuti perjalanan kami',
            'ms' => 'Ikuti perjalanan kami',
            'en' => 'Follow our journeys',
        ]);

        $this->migrator->update('footer.social_description', fn (): array => [
            'id' => 'Temukan inspirasi destinasi, kabar perjalanan, dan pembaruan paket melalui kanal resmi kami.',
            'ms' => 'Temui inspirasi destinasi, berita perjalanan, dan kemas kini pakej melalui saluran rasmi kami.',
            'en' => 'Discover destination inspiration, travel updates, and new packages through our official channels.',
        ]);

        $this->migrator->add('footer.contact_title', [
            'id' => 'Hubungi kami',
            'ms' => 'Hubungi kami',
            'en' => 'Contact us',
        ]);

        $this->migrator->add('footer.destinations_all_label', [
            'id' => 'Semua destinasi',
            'ms' => 'Semua destinasi',
            'en' => 'All destinations',
        ]);

        $this->migrator->add('footer.destination_limit', 5);

        $this->migrator->update('footer.link_groups', function (array $groups): array {
            $groups = json_decode(
                json_encode($groups, JSON_THROW_ON_ERROR),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );

            $groups = collect($groups)
                ->map(function (array $group): array {
                    $group['source'] ??= 'manual';

                    return $group;
                });

            $hasDestinations = $groups->contains(
                fn (array $group): bool => ($group['source'] ?? null) === 'destinations'
                    || mb_strtolower($group['title']['id'] ?? '') === 'destinasi',
            );

            if (! $hasDestinations) {
                $destinationGroup = [
                    'title' => ['id' => 'Destinasi', 'ms' => 'Destinasi', 'en' => 'Destinations'],
                    'source' => 'destinations',
                    'links' => [],
                ];

                $serviceIndex = $groups->search(
                    fn (array $group): bool => mb_strtolower($group['title']['id'] ?? '') === 'layanan',
                );
                $groups->splice($serviceIndex === false ? 0 : $serviceIndex + 1, 0, [$destinationGroup]);
            }

            $hasHelp = $groups->contains(
                fn (array $group): bool => mb_strtolower($group['title']['id'] ?? '') === 'bantuan',
            );

            if (! $hasHelp) {
                $groups->push([
                    'title' => ['id' => 'Bantuan', 'ms' => 'Bantuan', 'en' => 'Help'],
                    'source' => 'manual',
                    'links' => [
                        [
                            'label' => ['id' => 'Pertanyaan Umum', 'ms' => 'Soalan Lazim', 'en' => 'Frequently Asked Questions'],
                            'route' => 'home',
                            'fragment' => 'faq',
                            'url' => null,
                        ],
                        [
                            'label' => ['id' => 'Hubungi Kami', 'ms' => 'Hubungi Kami', 'en' => 'Contact Us'],
                            'route' => 'contact.index',
                            'fragment' => null,
                            'url' => null,
                        ],
                    ],
                ]);
            }

            return $groups->values()->all();
        });

    }
};
