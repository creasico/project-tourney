<?php

declare(strict_types=1);

use App\Enums\AgeRange;
use App\Enums\Gender;
use App\Filament\Resources\ClassificationResource\Pages\CreateClassification;
use App\Models\Classification;

use function Pest\Livewire\livewire;

it('can create a record', function () {
    $record = Classification::factory()->make();

    $page = livewire(CreateClassification::class);

    $page->fillForm([
        'label' => $record->label,
        'gender' => Gender::Male,
        'age_range' => AgeRange::Early,
        'weight_range' => '40-45',
    ])->call('create');

    $page->assertHasNoFormErrors();
});
