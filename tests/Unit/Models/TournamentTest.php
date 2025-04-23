<?php

declare(strict_types=1);

use App\Enums\TournamentStatus;
use App\Events\ParticipantDisqualified;
use App\Events\ParticipantKnockedOff;
use App\Events\ParticipantVerified;
use App\Models\Classification;
use App\Models\MatchGroup;
use App\Models\Matchup;
use App\Models\Participation;
use App\Models\Person;
use App\Models\Tournament;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

test('has many matches', function () {
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

test('has many classes', function () {
    $model = Tournament::factory()
        ->withClassifications()
        ->createOne();

    expect($model->classes)->toHaveCount(1);
    expect($model->divisions)->toHaveCount(1);

    $class = $model->classes->first();

    expect($class)->toBeInstanceOf(Classification::class);
    expect($class->group)->toBeInstanceOf(MatchGroup::class);

    expect($class->group->tournament->getKey())->toBe($model->getKey());
    expect($class->group->classification->getKey())->toBe($class->getKey());
});

test('has many participants', function () {
    $model = Tournament::factory()
        ->withParticipants()
        ->createOne();

    expect($model->participants)->toHaveCount(1);

    $participant = $model->participants->first();

    expect($participant)->toBeInstanceOf(Person::class);
    expect($participant->participation)->toBeInstanceOf(Participation::class);
});

test('could disqualify participants', function () {
    Event::fake();

    $model = Tournament::factory()
        ->withParticipants()
        ->createOne();

    $participant = $model->participants->first();

    expect($participant->participation->is_disqualified)->toBeFalse();

    $model->disqualify($participant, 'legit');

    Event::assertDispatched(ParticipantDisqualified::class);

    $model = $model->fresh();
    $participant = $model->participants->first();

    expect($participant->participation->is_disqualified)->toBeTrue();
});

test('could verify participants', function () {
    Event::fake();

    $model = Tournament::factory()
        ->withParticipants()
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

test('could knock-off participants', function () {
    Event::fake();

    $model = Tournament::factory()
        ->withParticipants()
        ->createOne();

    $participant = $model->participants->first();

    expect($participant->participation->is_knocked)->toBeFalse();

    $model->knockOff($participant, 'legit');

    Event::assertDispatched(ParticipantKnockedOff::class);

    $model = $model->fresh();
    $participant = $model->participants->first();

    expect($participant->participation->is_knocked)->toBeTrue();
});

test('could be drafted', function () {
    $model = Tournament::factory()->createOne([
        'start_date' => null,
        'finish_date' => null,
        'published_at' => null,
    ]);

    expect($model->is_draft)->toBeTrue();
    expect($model->status)->toBe(TournamentStatus::Draft);
    expect($model->status->isDraft())->toBeTrue();
});

test('could be scheduled', function () {
    $model = Tournament::factory()->createOne([
        'start_date' => Carbon::now()->addWeek(),
        'finish_date' => null,
        'published_at' => Carbon::now()->addDays(2),
    ]);

    expect($model->is_draft)->toBeFalse();
    expect($model->is_published)->toBeFalse();
    expect($model->status)->toBe(TournamentStatus::Scheduled);
    expect($model->status->isScheduled())->toBeTrue();
});

test('could be started', function () {
    $model = Tournament::factory()->createOne([
        'start_date' => Carbon::now()->subWeek(),
        'finish_date' => null,
        'published_at' => Carbon::now()->subWeeks(2),
    ]);

    expect($model->is_published)->toBeTrue();
    expect($model->status)->toBe(TournamentStatus::OnGoing);
    expect($model->status->isOnGoing())->toBeTrue();
});

test('could be finished', function () {
    $model = Tournament::factory()->createOne([
        'start_date' => Carbon::now()->subWeek(),
        'finish_date' => Carbon::now()->subDay(),
        'published_at' => Carbon::now()->subWeeks(2),
    ]);

    expect($model->is_finished)->toBeTrue();
    expect($model->status)->toBe(TournamentStatus::Finished);
    expect($model->status->isFinished())->toBeTrue();
});
