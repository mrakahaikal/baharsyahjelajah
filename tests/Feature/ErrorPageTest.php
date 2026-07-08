<?php

use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;

it('renders the branded 4xx error page for missing localized pages', function () {
    config(['app.debug' => false]);

    get('/id/halaman-tidak-ada')
        ->assertNotFound()
        ->assertSee('Baharsyah Jelajah')
        ->assertSee('Arah perjalanan ini belum tersedia.')
        ->assertSee('href="'.route('home', ['locale' => 'id']).'"', false)
        ->assertSee('href="'.route('tour.index', ['locale' => 'id']).'"', false)
        ->assertSee('href="'.route('blog.index', ['locale' => 'id']).'"', false)
        ->assertSee('href="'.route('contact.index', ['locale' => 'id']).'"', false);
});

it('falls back to Indonesian recovery links for invalid locale errors', function () {
    config(['app.debug' => false]);

    get('/fr/transport')
        ->assertNotFound()
        ->assertSee('Arah perjalanan ini belum tersedia.')
        ->assertSee('href="'.route('home', ['locale' => 'id']).'"', false)
        ->assertSee('href="'.route('contact.index', ['locale' => 'id']).'"', false);
});

it('renders the branded 5xx error page with recovery actions', function () {
    config(['app.debug' => false]);

    Route::get('/id/__server-error-preview', fn () => abort(500));

    get('/id/__server-error-preview')
        ->assertStatus(500)
        ->assertSee('Ada kendala sementara di sisi sistem.')
        ->assertSee('Muat ulang halaman')
        ->assertSee('href="'.route('contact.index', ['locale' => 'id']).'"', false);
});
