<?php

use Illuminate\Support\Facades\Blade;

it('renders each supported button variant', function (string $variant, string $expectedClass) {
    $html = Blade::render('<x-ui::button variant="'.$variant.'">Action</x-ui::button>');

    expect($html)
        ->toContain('<button')
        ->toContain('type="button"')
        ->toContain($expectedClass)
        ->toContain('Action');
})->with([
    'primary' => ['primary', 'bg-slate-950'],
    'secondary' => ['secondary', 'bg-blue-600'],
    'outline' => ['outline', 'border-slate-300'],
    'soft' => ['soft', 'bg-blue-50'],
    'ghost' => ['ghost', 'bg-transparent'],
    'danger' => ['danger', 'bg-red-600'],
    'inverse' => ['inverse', 'border-white/35'],
    'light' => ['light', 'bg-white'],
    'gold' => ['gold', 'bg-amber-400'],
    'gold outline' => ['gold-outline', 'border-amber-300/50'],
]);

it('renders leading and trailing icon slots', function () {
    $html = Blade::render(<<<'BLADE'
        <x-ui::button>
            <x-slot:icon><x-lucide-save /></x-slot:icon>
            Save changes
            <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
        </x-ui::button>
    BLADE);

    expect($html)
        ->toContain('Save changes')
        ->toContain('aria-hidden="true"');

    expect(substr_count($html, '<svg'))->toBe(2);
});

it('automatically renders a targeted livewire loading state for button actions', function () {
    $html = Blade::render(<<<'BLADE'
        <x-ui::button wire:click="save" loading-text="Saving…">
            Save changes
        </x-ui::button>
    BLADE);

    expect($html)
        ->toContain('wire:click="save"')
        ->toContain('wire:loading.attr="disabled"')
        ->toContain('wire:target="save"')
        ->toContain('wire:loading.remove')
        ->toContain('wire:loading.flex')
        ->toContain('role="status"')
        ->toContain('Saving…')
        ->toContain('animate-spin');
});

it('supports an explicit loading target for submit buttons', function () {
    $html = Blade::render(<<<'BLADE'
        <x-ui::button type="submit" :loading="true" loading-target="submitBooking">
            Submit booking
        </x-ui::button>
    BLADE);

    expect($html)
        ->toContain('type="submit"')
        ->toContain('wire:target="submitBooking"')
        ->toContain('wire:loading.attr="disabled"');
});

it('keeps links free from button and livewire loading attributes', function () {
    $html = Blade::render(<<<'BLADE'
        <x-ui::button tag="a" href="/contact" :loading="true">
            Contact us
        </x-ui::button>
    BLADE);

    expect($html)
        ->toContain('<a')
        ->toContain('href="/contact"')
        ->not->toContain('type="button"')
        ->not->toContain('wire:loading');
});
