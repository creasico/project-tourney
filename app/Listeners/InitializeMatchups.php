<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AthletesParticipated;
use App\Jobs\CalculateMatchups;
use Sentry;
use Sentry\State\Scope;
use Throwable;

final class InitializeMatchups
{
    public function handle(AthletesParticipated $event): void
    {
        $tournament = $event->tournament->fresh();

        if ($tournament->is_draft) {
            return;
        }

        dispatch(new CalculateMatchups($tournament, $event->classId));
    }

    /**
     * @codeCoverageIgnore
     */
    public function failed(AthletesParticipated $event, Throwable $error): void
    {
        Sentry\withScope(function (Scope $scope) use ($error, $event) {
            $context = [
                'tournament_id' => $event->tournament->id,
                'class_id' => $event->classId,
            ];

            if (method_exists($error, 'context')) {
                $context = array_merge($context, $error->context());
            }

            $scope->setContext($event::class, $context)
                ->setTag('class_id', $event->classId)
                ->setTag('tournament_id', $event->tournament->id);

            Sentry\captureException($error);
        });
    }
}
