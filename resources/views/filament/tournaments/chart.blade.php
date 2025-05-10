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
            $division = $group->divisions->where('id', $div)->first();
            @endphp

            <div class="leading-6 rounded-lg px-4 py-3 bg-gray-50 dark:bg-white/5 ring-1 ring-gray-950/5 dark:ring-white/10">{{ $division->label }}</div>

            <div class="flex gap-1">
                @foreach ($divisionMatches->groupBy('round_number') as $round => $roundMatches)
                    <section id="{{ $round }}" class="rounds flex flex-col gap-4 px-3 border-l-2 first:border-l-0 border-dashed border-gray-50 dark:border-white/5">
                        <h3 class="leading-6">Round {{ $round }}</h3>

                        <div class="matches grid grid-cols-[repeat(var(--grid),1fr)] gap-[--gap] w-[--width]">
                            @foreach ($roundMatches as $match)
                                @if ($match->attr->gap > 0)
                                    @foreach (range(1, $match->attr->gap) as $g)
                                    <x-match-party :hidden="true" />
                                    @endforeach
                                @endif

                                <x-match-party :match="$match"/>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
