<?php

declare(strict_types=1);

use App\Enums\AgeRange;
use App\Enums\Gender;
use App\Imports\TournamentAthleteImport;
use App\Models\Classification;
use App\Models\Person;

it('should be able to use existing athlete', function () {
    $class = Classification::factory()
        ->withGender(Gender::Male)
        ->withRange(AgeRange::Junior)
        ->createOne();

    $person = Person::factory()
        ->asAthlete(withClassification: $class)
        ->withGender($class->gender)
        ->withContinent()
        ->createOne();

    /** @var TournamentAthleteImport */
    $mock = (new ReflectionClass(TournamentAthleteImport::class))
        ->newInstanceWithoutConstructor();

    $athlete = $mock->toAthlete(
        category: 'tanding',
        classification: $class->label,
        continent: $person->continent->name,
        name: $person->name,
        gender: $person->gender->label(),
        ageRange: $class->age_range->label(),
    );

    expect($athlete->classification->getKey())->toBe($person->classification->getKey());
    expect($athlete->continent->getKey())->toBe($person->continent->getKey());
    expect($athlete->getKey())->toBe($person->getKey());

    expect($athlete->wasRecentlyCreated)->toBeFalse();
});

it('should be able to create new athlete if not exists', function () {
    /** @var TournamentAthleteImport */
    $mock = (new ReflectionClass(TournamentAthleteImport::class))
        ->newInstanceWithoutConstructor();

    $athlete = $mock->toAthlete(
        category: 'tanding',
        classification: 'sample class',
        continent: 'sample continent',
        name: 'khusnul aslih',
        gender: Gender::Male->label(),
        ageRange: AgeRange::Junior->label(),
    );

    expect($athlete->wasRecentlyCreated)->toBeTrue();
});
