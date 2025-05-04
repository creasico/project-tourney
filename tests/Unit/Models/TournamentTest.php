<?php

declare(strict_types=1);

use App\Enums\TimelineStatus;
use App\Events\ParticipantDisqualified;
use App\Events\ParticipantKnockedOff;
use App\Events\ParticipantVerified;
use App\Models\Classification;
use App\Models\Division;
use App\Models\MatchGroup;
use App\Models\Matchup;
use App\Models\Participation;
use App\Models\Person;
use App\Models\Tournament;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

it('has many matches', function () {
    $model = Tournament::factory()
        ->has(
            Matchup::factory(2),
            'matches',
        )
        ->createOne();

    expect($model->matches)->toHaveCount(2);

    $match = $model->matches->first();

    expect($match)->toBeInstanceOf(Matchup::class);
});

it('has many divisions', function () {
    $model = Tournament::factory()
        ->has(
            Division::factory(2),
            'divisions',
        )
        ->createOne();

    expect($model->divisions)->toHaveCount(2);

    $division = $model->divisions->first();

    expect($division)->toBeInstanceOf(Division::class);
});

it('has many classes', function () {
    $model = Tournament::factory()
        ->withClassifications()
        ->createOne();

    expect($model->classes)->toHaveCount(1);
    expect($model->groups)->toHaveCount(1);

    $class = $model->classes->first();

    expect($class)->toBeInstanceOf(Classification::class);
    expect($class->group)->toBeInstanceOf(MatchGroup::class);

    expect($class->group->tournament->getKey())->toBe($model->getKey());
    expect($class->group->classification->getKey())->toBe($class->getKey());
});

it('has many participants', function () {
    $model = Tournament::factory()
        ->withAthletes()
        ->createOne();

    expect($model->participants)->toHaveCount(1);

    $participant = $model->participants->first();

    expect($participant)->toBeInstanceOf(Person::class);
    expect($participant->participation)->toBeInstanceOf(Participation::class);
});

it('could disqualify participants', function () {
    Event::fake();

    $model = Tournament::factory()
        ->withAthletes()
        ->createOne();

    $participant = $model->participants->first();

    expect($participant->participation->is_disqualified)->toBeFalse();

    $model->disqualify($participant, 'legit');

    Event::assertDispatched(ParticipantDisqualified::class);

    $model = $model->fresh();
    $participant = $model->participants->first();

    expect($participant->participation->is_disqualified)->toBeTrue();
});

it('could verify participants', function () {
    Event::fake();

    $model = Tournament::factory()
        ->withAthletes()
        ->createOne();

    expect($model->unverifiedParticipants)->toHaveCount(1);
    expect($model->verifiedParticipants)->toHaveCount(0);

    $participant = $model->participants->first();

    expect($participant->participation->is_verified)->toBeFalse();

    $model->verify($participant, 'legit');

    Event::assertDispatched(ParticipantVerified::class);

    $model = $model->fresh();
    $participant = $model->participants->first();

    expect($model->unverifiedParticipants)->toHaveCount(0);
    expect($model->verifiedParticipants)->toHaveCount(1);

    expect($participant->participation->is_verified)->toBeTrue();
});

it('could knock-off participants', function () {
    Event::fake();

    $model = Tournament::factory()
        ->withAthletes()
        ->createOne();

    $participant = $model->participants->first();

    expect($participant->participation->is_knocked)->toBeFalse();

    $model->knockOff($participant, 'legit');

    Event::assertDispatched(ParticipantKnockedOff::class);

    $model = $model->fresh();
    $participant = $model->participants->first();

    expect($participant->participation->is_knocked)->toBeTrue();
});

it('could be drafted', function () {
    $model = Tournament::factory()->createOne([
        'finish_date' => null,
        'published_at' => null,
    ]);

    expect($model)
        ->is_draft->toBeTrue()
        ->status->toBe(TimelineStatus::Draft);

    expect($model->status->isDraft())->toBeTrue();
});

it('could be scheduled', function () {
    $model = Tournament::factory()->createOne([
        'published_at' => Carbon::now()->addDay(),
        'start_date' => Carbon::now()->addDay(),
        'finish_date' => null,
    ]);

    expect($model)
        ->is_draft->toBeFalse()
        ->is_published->toBeFalse()
        ->status->toBe(TimelineStatus::Scheduled);

    expect($model->status->isScheduled())->toBeTrue();
});

it('could be started', function () {
    $model = Tournament::factory()->createOne([
        'start_date' => Carbon::now()->subWeek(),
        'finish_date' => null,
        'published_at' => Carbon::now()->subWeeks(2),
    ]);

    expect($model)
        ->is_started->toBeTrue()
        ->is_published->toBeTrue()
        ->is_finished->toBeFalse()
        ->status->toBe(TimelineStatus::Started);

    expect($model->status->isStarted())->toBeTrue();
});

it('could be finished', function () {
    $model = Tournament::factory()->finished()->createOne();

    expect($model)
        ->is_finished->toBeTrue()
        ->status->toBe(TimelineStatus::Finished);

    expect($model->status->isFinished())->toBeTrue();
});
