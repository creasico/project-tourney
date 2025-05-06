<?php

declare(strict_types=1);

namespace App\Models\Helpers;

use App\Enums\TimelineStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @property-read \App\Enums\TimelineStatus $status
 */
trait WithTimelineStatus
{
    public function status(): Attribute
    {
        return Attribute::get(function (): TimelineStatus {
            if ($this->is_draft) {
                return TimelineStatus::Draft;
            }

            if ($this->is_finished) {
                return TimelineStatus::Finished;
            }

            if ($this->is_going) {
                return TimelineStatus::Started;
            }

            return TimelineStatus::Scheduled;
        });
    }

    public function isGoing(): Attribute
    {
        return Attribute::get(
            fn (): bool => $this->is_started && ! $this->is_finished
        );
    }

    public function isStarted(): Attribute
    {
        return Attribute::get(
            fn (): bool => $this->{$this->getStartedTimeColumn()}?->lt(now()) ?: false
        );
    }

    public function isFinished(): Attribute
    {
        return Attribute::get(
            fn (): bool => $this->{$this->getFinishedAtColumn()}?->lt(now()) ?: false
        );
    }

    public function isDraft(): Attribute
    {
        return Attribute::get(
            fn (): bool => $this->{$this->getDraftedTimeColumn()} === null
        );
    }

    protected function getDraftedTimeColumn(): string
    {
        return $this->timelineColumns['draft'] ?? 'started_at';
    }

    protected function getStartedTimeColumn(): string
    {
        return $this->timelineColumns['start'] ?? 'started_at';
    }

    protected function getFinishedAtColumn(): string
    {
        return $this->timelineColumns['finish'] ?? 'finished_at';
    }

    public function markAsStarted(?CarbonInterface $now = null)
    {
        $now ??= now();

        $updated = $this->update([
            $this->getStartedTimeColumn() => $now,
        ]);

        if (isset($this->timelineEvents['start']) && class_exists($this->timelineEvents['start'])) {
            event(new $this->timelineEvents['start']($this));
        }

        return $updated;
    }

    public function markAsFinished(?CarbonInterface $now = null)
    {
        $now ??= now();
        $attrs = [
            $this->getFinishedAtColumn() => $now,
        ];

        if ($this->{$this->getStartedTimeColumn()} === null) {
            $attrs[$this->getStartedTimeColumn()] = $now;
        }

        $updated = $this->update($attrs);

        if (isset($this->timelineEvents['finish']) && class_exists($this->timelineEvents['finish'])) {
            event(new $this->timelineEvents['finish']($this));
        }

        return $updated;
    }
}
