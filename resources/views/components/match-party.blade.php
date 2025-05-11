<?php
/**
 * @var \App\Models\Matchup $match
 * @var \App\Support\Athlete $blue
 * @var \App\Support\Athlete $red
 */
?>

@props([
    'match',
    'hidden' => false,
    'final' => false,
])

<div
    style="{{ sprintf('--size: %d; --next-round: %d;', $match->attr->size, $match->next?->round_number) }}"
    data-next-side="{{ $match->next_side }}"
    data-match="{{ $match->party_number }}"
    aria-hidden="{{ $hidden ? 'true' : 'false' }}"
    aria-disabled="{{ $match->is_proceeded ? 'true' : 'false' }}"
    @class([
        'match z-5 flex relative box-border pl-7 pr-1 items-center',
        'proceed cursor-not-allowed' => $match->is_proceeded,
        'final-round' => $final,
    ])
>
    @if (! $hidden)
        <div class="match-title absolute w-6 h-6 rounded-full leading-none text-center items-center content-center font-bold tabular-nums bg-gray-100 dark:bg-white/5 ring-1 ring-gray-900/10 dark:ring-white/10">{{ $match->party_number }}</div>

        <div
            class="match-party bg-white dark:bg-gray-900 py-1 w-full isolate flex flex-col gap-2 content-center align-middle"
            data-next-side="{{ $match->next_side }}"
        >
            <div
                data-side="blue"
                aria-label="{{ $match->blue_side->getAriaLabel() }}"
                @class([
                    'flex flex-col content-center justify-items-center h-16 py-2 px-3 shadow-sm rounded-lg bg-blue-100 dark:bg-blue-700/10 ring-1 ring-blue-900/30 dark:ring-blue-100/10',
                    'text-gray-900/50 dark:text-gray-50/50 line-through' => $match->blue_side->status->isLose(),
                    'text-gray-900 dark:text-gray-50' => $match->blue_side->canProceed(),
                ])
            >
                <span class="party-name font-bold truncate leading-7">{{ $match->blue_side->display }}</span>

                @if ($match->blue_side->continentName)
                <span class="party-continent truncate leading-7 text-sm text-gray-500">{{ $match->blue_side->continentName }}</span>
                @endif
            </div>

            <div
                data-side="red"
                aria-label="{{ $match->red_side->getAriaLabel() }}"
                @class([
                    'flex flex-col content-center justify-items-center h-16 py-2 px-3 shadow-sm rounded-lg bg-red-100 dark:bg-red-700/10 ring-1 ring-red-900/30 dark:ring-red-100/10',
                    'text-gray-900/50 dark:text-gray-50/50 line-through' => $match->red_side->status->isLose(),
                    'text-gray-900 dark:text-gray-50' => $match->red_side->canProceed(),
                ])
            >
                <span class="party-name font-bold truncate leading-7">{{ $match->red_side?->display }}</span>

                @if ($match->red_side?->continentName)
                <span class="party-continent truncate leading-7 text-sm text-gray-500">{{ $match->red_side?->continentName }}</span>
                @endif
            </div>
        </div>
    @endif
</div>
