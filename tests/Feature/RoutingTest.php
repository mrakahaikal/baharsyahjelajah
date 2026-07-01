<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('redirects from root to default locale when no supported language is detected', function () {
    get('/', ['Accept-Language' => 'fr-FR,fr;q=0.9'])
        ->assertRedirect('/id');
});

it('redirects from root to the detected supported locale', function () {
    get('/', ['Accept-Language' => 'ms-MY,ms;q=0.9,en;q=0.8'])
        ->assertRedirect('/ms');
});

it('sets the application locale based on the URL prefix', function (string $locale) {
    get("/{$locale}")
        ->assertStatus(200)
        ->assertSee("<html lang=\"{$locale}\"", false);
})->with(['id', 'ms', 'en']);

it('renders the expanded OTA homepage sections', function () {
    get('/id')
        ->assertOk()
        ->assertSee('Cari layanan perjalanan')
        ->assertSee('Layanan utama')
        ->assertSee("Nuansa Ka'bah premium", false)
        ->assertSee('Konsultasi gratis')
        ->assertSee('Baharsyah Jelajah');
});

it('returns 404 for invalid locales', function () {
    get('/fr/transport')
        ->assertStatus(404);
});

it('renders the correct page view', function (string $path, string $content) {
    get("/id/{$path}")
        ->assertStatus(200)
        ->assertSee($content);
})->with([
    ['transport', 'Transport Index'],
    ['tour', 'Paket Tour Wisata Halal'],
    ['umroh', 'Umroh Index'],
    ['blog', 'Blog Index'],
    ['visa', 'Visa Page'],
    ['shop', 'Shop Index'],
    ['gallery', 'Gallery Page'],
    ['testimonials', 'Testimonials Page'],
]);
