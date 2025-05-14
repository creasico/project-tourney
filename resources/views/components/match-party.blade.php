<?php
/**
 * @var \App\Models\Matchup $match
 * @var bool $final
 */
?>

@props([
    'match',
    'final' => false,
])

<div
    style="{{ sprintf('--size: %d; --next-round: %d;', $match->attr?->size, $match->next?->round_number) }}"
    data-next-side="{{ $match->next_side }}"
    data-match="{{ $match->party_number }}"
    aria-disabled="{{ $match->is_proceeded ? 'true' : 'false' }}"
    @class([
        'match z-5 flex relative box-border pl-7 pr-1 items-center',
        'proceed cursor-not-allowed' => $match->is_proceeded,
        'final-round' => $final,
    ])
>
    <div class="match-title absolute w-6 h-6 rounded-full leading-none text-center items-center content-center font-bold tabular-nums bg-gray-100 dark:bg-white/5 ring-1 ring-gray-900/10 dark:ring-white/10">{{ $match->party_number }}</div>

    <div class="match-party bg-white dark:bg-gray-900 py-1 w-full isolate flex flex-col gap-2 content-center align-middle" data-next-side="{{ $match->next_side }}">
        <x-match-athlete side="blue" :athlete="$match->blue_side" />

        <x-match-athlete side="red" :athlete="$match->red_side" />
    </div>
</div>
