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

@php
$classes = 'match z-50 flex relative box-border pl-7 pr-1 items-center';

if ($final) {
    $classes .= ' final-round';
}
@endphp

<div
    style="{{ sprintf('--size: %d; --next-round: %d;', $match->attr->size, $match->next?->round_number) }}"
    data-next-side="{{ $match->next_side }}"
    data-match="{{ $match->party_number }}"
    aria-hidden="{{ $hidden ? 'true' : 'false' }}"
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if (! $hidden)
        <div class="match-title absolute w-6 h-6 rounded-full leading-none text-center items-center content-center font-bold tabular-nums bg-gray-50 dark:bg-white/5 ring-1 ring-gray-950/5 dark:ring-white/10">{{ $match->party_number }}</div>

        <div
            class="match-party bg-gray-200 dark:bg-gray-900 py-1 w-full isolate flex flex-col gap-2 content-center align-middle"
            data-next-side="{{ $match->next_side }}"
        >
            <div
                class="flex flex-col content-center justify-items-center h-16 py-2 px-3 shadow-sm rounded-lg bg-gray-50 dark:bg-white/5 ring-1 ring-gray-950/5 dark:ring-white/10"
                data-side="blue"
            >
                <span class="party-name font-bold">{{ $match->blue_side->display }}</span>

                @if ($match->blue_side->continentName)
                <span class="party-continent text-sm text-gray-500">{{ $match->blue_side->continentName }}</span>
                @endif
            </div>

            <div
                class="flex flex-col content-center justify-items-center h-16 py-2 px-3 shadow-sm rounded-lg bg-gray-50 dark:bg-white/5 ring-1 ring-gray-950/5 dark:ring-white/10"
                data-side="red"
            >
                <span class="party-name font-bold">{{ $match->red_side?->display }}</span>

                @if ($match->red_side?->continentName)
                <span class="party-continent text-sm text-gray-500">{{ $match->red_side?->continentName }}</span>
                @endif
            </div>
        </div>
    @endif
</div>
