<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('redirects from root to default locale', function () {
    get('/')
        ->assertRedirect('/id');
});

it('sets the application locale based on the URL prefix', function (string $locale) {
    get("/{$locale}")
        ->assertStatus(200)
        ->assertSee("<html lang=\"{$locale}\"", false);
})->with(['id', 'ms', 'en']);

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
    ['tour', 'Tour Index'],
    ['umroh', 'Umroh Index'],
    ['blog', 'Blog Index'],
    ['visa', 'Visa Page'],
    ['shop', 'Shop Index'],
    ['gallery', 'Gallery Page'],
    ['testimonials', 'Testimonials Page'],
]);