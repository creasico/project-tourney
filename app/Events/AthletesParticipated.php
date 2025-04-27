<?php

namespace App\Events;

use App\Models\Classification;
use App\Models\MatchGroup;
use App\Models\Tournament;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AthletesParticipated
{
    use Dispatchable, SerializesModels;

    public readonly Classification $class;

    public readonly MatchGroup $group;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Tournament $tournament,
        Classification|string $class,
    ) {
        $this->class = $tournament->classes
            ->where('id', is_string($class) ? $class : $class->id)
            ->first();

        $this->group = $this->class->group;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     *
     * @codeCoverageIgnore
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
