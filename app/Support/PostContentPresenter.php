<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Str;

class PostContentPresenter
{
    private const WORDS_PER_MINUTE = 200;

    /**
     * @return array{
     *     html: string,
     *     headings: array<int, array{id: string, level: int, title: string}>,
     *     readingMinutes: int
     * }
     */
    public function present(string $html): array
    {
        $plainText = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $words = $plainText === '' ? [] : preg_split('/\s+/u', $plainText, -1, PREG_SPLIT_NO_EMPTY);
        $readingMinutes = max(1, (int) ceil(count($words ?: []) / self::WORDS_PER_MINUTE));

        if (trim($html) === '') {
            return ['html' => '', 'headings' => [], 'readingMinutes' => $readingMinutes];
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorState = libxml_use_internal_errors(true);
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="post-content-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorState);

        if (! $loaded) {
            return ['html' => $html, 'headings' => [], 'readingMinutes' => $readingMinutes];
        }

        $root = $document->getElementById('post-content-root');

        if (! $root) {
            return ['html' => $html, 'headings' => [], 'readingMinutes' => $readingMinutes];
        }

        $headings = [];
        $usedIds = [];

        $headingNodes = (new DOMXPath($document))->query('//*[@id="post-content-root"]//h2 | //*[@id="post-content-root"]//h3');

        if ($headingNodes) {
            /** @var DOMElement $heading */
            foreach ($headingNodes as $heading) {
                $title = Str::squish($heading->textContent);

                if ($title === '') {
                    continue;
                }

                $baseId = Str::slug($title) ?: 'bagian-artikel';
                $usedIds[$baseId] = ($usedIds[$baseId] ?? 0) + 1;
                $id = $usedIds[$baseId] === 1 ? $baseId : $baseId.'-'.$usedIds[$baseId];
                $heading->setAttribute('id', $id);

                $headings[] = [
                    'id' => $id,
                    'level' => (int) substr($heading->tagName, 1),
                    'title' => $title,
                ];
            }
        }

        $presentedHtml = '';

        foreach ($root->childNodes as $childNode) {
            $presentedHtml .= $document->saveHTML($childNode);
        }

        return [
            'html' => $presentedHtml,
            'headings' => $headings,
            'readingMinutes' => $readingMinutes,
        ];
    }
}
