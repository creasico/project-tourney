<?php
/**
 * @var \App\Models\Classification $record
 * @var \App\Models\Tournament $ownerRecord
 * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Matchup> $matches
 * @var \App\Models\MatchGroup $group
 */
?>

<div class="flex flex-col gap-8" style="--height: 145px; --width: 250px; --gap: 1em;">
    @foreach ($matches->groupBy('division_id') as $div => $divisionMatches)
        <section class="flex flex-col gap-4">
            @php
            $division = $group->divisions->firstWhere('id', $div);
            @endphp

            <div class="leading-6 rounded-lg sticky top-20 z-10 px-4 py-3 bg-gray-100 dark:bg-gray-800 ring-1 ring-gray-900/10 dark:ring-white/10">{{ $division->label }}</div>

            <div class="overflow-x-scroll pb-2">
                <div class="flex gap-1">
                    @foreach ($divisionMatches->groupBy('round_number') as $index => $roundMatches)
                        @php
                        $round = $division->getRound($index)
                        @endphp

                        <section
                            id="round-{{ $index }}"
                            style="--current-round: {{ $index }};"
                            class="rounds flex flex-col gap-4 px-3 border-l-2 first:border-l-0 border-dashed border-gray-100 dark:border-white/5"
                        >
                            <h3 class="leading-6 font-bold pl-4">{{ $round->getLabel() }}</h3>

                            <div class="matches grid gap-[--gap] w-[--width]" style="--grid: {{ $division->getGrid($index, $roundMatches) }}">
                                @foreach ($roundMatches as $match)
                                    @foreach ($match->gaps as $g)
                                    <div class="match" aria-hidden="true"></div>
                                    @endforeach

                                    <x-match-party :match="$match" :final="$round->isFinal()" />
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            </div>
        </section>
    @endforeach
</div>
