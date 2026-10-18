<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Concerns;

use Illuminate\Support\Str;

trait NormalizesMultilingualInput
{
    /**
     * Normalize string or associative array/json to a standard ['id' => ..., 'en' => ..., 'ms' => ...] array.
     *
     * @return array<string, string>
     */
    protected function normalizeMultilingualInput(mixed $input): array
    {
        if (is_string($input)) {
            $trimmed = trim($input);
            if (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    $input = $decoded;
                }
            }
        }

        if (is_array($input)) {
            $primary = (string) ($input['id'] ?? $input['en'] ?? $input['ms'] ?? '');

            return [
                'id' => (string) ($input['id'] ?? $primary),
                'en' => (string) ($input['en'] ?? $primary),
                'ms' => (string) ($input['ms'] ?? $primary),
            ];
        }

        $stringValue = trim((string) $input);

        return [
            'id' => $stringValue,
            'en' => $stringValue,
            'ms' => $stringValue,
        ];
    }

    /**
     * Resolve slug translations from input or title/name translations.
     *
     * @param  array<string, string>  $nameOrTitle
     * @return array<string, string>
     */
    protected function resolveSlugTranslations(array $nameOrTitle, mixed $rawSlug): array
    {
        if (! empty($rawSlug)) {
            $normalized = $this->normalizeMultilingualInput($rawSlug);

            return [
                'id' => Str::slug($normalized['id'] ?: $nameOrTitle['id']),
                'en' => Str::slug($normalized['en'] ?: ($nameOrTitle['en'] ?: $nameOrTitle['id'])),
                'ms' => Str::slug($normalized['ms'] ?: ($nameOrTitle['ms'] ?: $nameOrTitle['id'])),
            ];
        }

        return [
            'id' => Str::slug($nameOrTitle['id']),
            'en' => Str::slug($nameOrTitle['en'] ?: $nameOrTitle['id']),
            'ms' => Str::slug($nameOrTitle['ms'] ?: $nameOrTitle['id']),
        ];
    }

    /**
     * Ensure content is formatted as semantic RichText HTML instead of plain text or raw textarea output.
     * If plain text or markdown is detected, converts it into semantic HTML elements (<p>, <h2>, <ul>, etc.).
     */
    protected function ensureRichTextHtml(string $content): string
    {
        $trimmed = trim($content);
        if ($trimmed === '') {
            return '';
        }

        // Check if string already contains block HTML tags commonly produced by RichText editors
        $hasBlockHtml = (bool) preg_match('/<(?:p|h[1-6]|ul|ol|li|blockquote|table|pre|div|section|article)\b[^>]*>/i', $trimmed);

        if ($hasBlockHtml) {
            return $trimmed;
        }

        // Convert plain text or markdown into clean semantic RichText HTML
        return trim(Str::markdown($trimmed));
    }

    /**
     * Normalize multilingual input specifically for RichText HTML content.
     *
     * @return array<string, string>
     */
    protected function normalizeRichTextMultilingual(mixed $input): array
    {
        $normalized = $this->normalizeMultilingualInput($input);

        return [
            'id' => $this->ensureRichTextHtml($normalized['id']),
            'en' => $this->ensureRichTextHtml($normalized['en']),
            'ms' => $this->ensureRichTextHtml($normalized['ms']),
        ];
    }

    /**
     * Estimate reading time in minutes based on ~200 words per minute.
     */
    protected function calculateReadingTime(string $text): int
    {
        $words = str_word_count(strip_tags($text));

        return (int) max(1, ceil($words / 200));
    }
}
