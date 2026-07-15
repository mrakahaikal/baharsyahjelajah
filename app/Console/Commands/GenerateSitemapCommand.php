<?php

namespace App\Console\Commands;

use App\Services\SitemapService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('sitemap:generate')]
#[Description('Generate the website sitemap and write it to the public directory')]
class GenerateSitemapCommand extends Command
{
    public function handle(SitemapService $sitemapService): int
    {
        try {
            $sitemapPath = $sitemapService->generate();
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Gagal membuat sitemap: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Sitemap berhasil dibuat di {$sitemapPath}.");

        return self::SUCCESS;
    }
}
