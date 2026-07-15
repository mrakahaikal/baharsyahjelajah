<?php

namespace App\View\Components\Shared;

use App\Models\Destination;
use App\Settings\FooterSettings;
use App\Settings\GeneralSettings;
use App\Settings\SocialSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class Footer extends Component
{
    public string $locale;

    public string $brandDescription;

    public string $ctaTitle;

    public string $ctaDescription;

    public string $ctaLabel;

    public ?string $ctaUrl;

    public string $socialTitle;

    public string $socialDescription;

    public string $contactTitle;

    public string $copyrightText;

    /** @var array<int, array{title: string, source: string, links: array<int, array{label: string, url: string}>}> */
    public array $linkGroups;

    /** @var array<int, array{label: string, value: string, url: string, icon: string, external: bool}> */
    public array $contactItems;

    /** @var array<int, array{label: string, url: string, icon: string}> */
    public array $socialLinks;

    /** @var array<int, array{label: string, url: string}> */
    public array $legalLinks;

    public function __construct(
        FooterSettings $footerSettings,
        GeneralSettings $generalSettings,
        SocialSettings $socialSettings,
    ) {
        $this->locale = app()->getLocale();
        $this->brandDescription = $this->localized($footerSettings->brand_description);
        $this->ctaTitle = $this->localized($footerSettings->cta_title);
        $this->ctaDescription = $this->localized($footerSettings->cta_subtitle);
        $this->ctaLabel = $this->localized($footerSettings->cta_button_label);
        $this->ctaUrl = $this->routeUrl($footerSettings->cta_button_route);
        $this->socialTitle = $this->localized($footerSettings->social_title);
        $this->socialDescription = $this->localized($footerSettings->social_description);
        $this->contactTitle = $this->localized($footerSettings->contact_title);
        $this->copyrightText = $this->localized($footerSettings->copyright_text);
        $this->linkGroups = $this->buildLinkGroups($footerSettings);
        $this->contactItems = $this->buildContactItems($generalSettings);
        $this->socialLinks = $this->buildSocialLinks($socialSettings);
        $this->legalLinks = $this->buildManualLinks($footerSettings->legal_links);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.shared.footer');
    }

    /** @return array<int, array{title: string, source: string, links: array<int, array{label: string, url: string}>}> */
    private function buildLinkGroups(FooterSettings $settings): array
    {
        return collect($settings->link_groups)
            ->map(function (array $group) use ($settings): array {
                $links = ($group['source'] ?? 'manual') === 'destinations'
                    ? $this->destinationLinks($settings)
                    : $this->buildManualLinks($group['links'] ?? []);

                return [
                    'title' => $this->localized($group['title'] ?? []),
                    'source' => $group['source'] ?? 'manual',
                    'links' => $links,
                ];
            })
            ->filter(fn (array $group): bool => filled($group['title']) && $group['links'] !== [])
            ->values()
            ->all();
    }

    /** @return array<int, array{label: string, url: string}> */
    private function destinationLinks(FooterSettings $settings): array
    {
        $destinations = Destination::query()
            ->select(['id', 'name', 'slug'])
            ->active()
            ->whereHas('itineraries.tourPackage.tour', fn (Builder $query): Builder => $query->active())
            ->get()
            ->sortBy(fn (Destination $destination): string => $destination->name, SORT_NATURAL | SORT_FLAG_CASE)
            ->take(max(1, $settings->destination_limit))
            ->map(fn (Destination $destination): array => [
                'label' => $destination->name,
                'url' => route('destination.show', [
                    'locale' => $this->locale,
                    'destination' => $destination,
                ]),
            ]);

        if ($destinations->isNotEmpty()) {
            $destinations->push([
                'label' => $this->localized($settings->destinations_all_label),
                'url' => route('destination.index', ['locale' => $this->locale]),
            ]);
        }

        return $destinations->values()->all();
    }

    /** @param array<int, array<string, mixed>> $links
     * @return array<int, array{label: string, url: string}>
     */
    private function buildManualLinks(array $links): array
    {
        return collect($links)
            ->map(function (array $link): ?array {
                $url = $this->linkUrl($link);
                $label = $this->localized($link['label'] ?? []);

                return filled($url) && filled($label) ? compact('label', 'url') : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    /** @return array<int, array{label: string, value: string, url: string, icon: string, external: bool}> */
    private function buildContactItems(GeneralSettings $settings): array
    {
        return collect([
            filled($settings->whatsapp_number) ? [
                'label' => 'WhatsApp',
                'value' => '+'.$settings->whatsapp_number,
                'url' => 'https://wa.me/'.$settings->whatsapp_number,
                'icon' => 'message',
                'external' => true,
            ] : null,
            filled($settings->email) ? [
                'label' => 'Email',
                'value' => $settings->email,
                'url' => 'mailto:'.$settings->email,
                'icon' => 'mail',
                'external' => false,
            ] : null,
            filled($this->localized($settings->address)) ? [
                'label' => $this->contactTitle,
                'value' => $this->localized($settings->address),
                'url' => '',
                'icon' => 'map',
                'external' => false,
            ] : null,
        ])->filter()->values()->all();
    }

    /** @return array<int, array{label: string, url: string, icon: string}> */
    private function buildSocialLinks(SocialSettings $settings): array
    {
        return collect([
            ['label' => 'Instagram', 'url' => $this->socialUrl($settings->instagram, 'instagram'), 'icon' => 'instagram'],
            ['label' => 'Facebook', 'url' => $this->socialUrl($settings->facebook, 'facebook'), 'icon' => 'facebook'],
            ['label' => 'TikTok', 'url' => $this->socialUrl($settings->tiktok, 'tiktok'), 'icon' => 'music'],
            ['label' => 'YouTube', 'url' => $this->socialUrl($settings->youtube, 'youtube'), 'icon' => 'youtube'],
        ])->filter(fn (array $link): bool => filled($link['url']))->values()->all();
    }

    /** @param array<string, mixed> $link */
    private function linkUrl(array $link): ?string
    {
        if (filled($link['url'] ?? null)) {
            return $link['url'];
        }

        $url = $this->routeUrl($link['route'] ?? null);

        if (! filled($url)) {
            return null;
        }

        return filled($link['fragment'] ?? null)
            ? $url.'#'.ltrim($link['fragment'], '#')
            : $url;
    }

    private function routeUrl(?string $routeName): ?string
    {
        return filled($routeName) && Route::has($routeName)
            ? route($routeName, ['locale' => $this->locale])
            : null;
    }

    private function socialUrl(?string $value, string $platform): ?string
    {
        if (! filled($value)) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        $handle = ltrim($value, '@');

        return match ($platform) {
            'instagram' => 'https://instagram.com/'.$handle,
            'facebook' => 'https://facebook.com/'.$handle,
            'tiktok' => 'https://tiktok.com/@'.$handle,
            'youtube' => 'https://youtube.com/@'.$handle,
            default => null,
        };
    }

    /** @param array<string, string>|string|null $value */
    private function localized(array|string|null $value): string
    {
        if (! is_array($value)) {
            return (string) $value;
        }

        return (string) ($value[$this->locale]
            ?? $value['id']
            ?? $value['en']
            ?? collect($value)->first(fn (?string $translation): bool => filled($translation))
            ?? '');
    }
}
