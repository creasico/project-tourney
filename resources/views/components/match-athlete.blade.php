@props([
    'athlete',
    'side'
])

@php
/**
 * @var \App\Support\Athlete $athlete
 * @var 'blue'|'red' $side
 */

$styles = [
    'blue' => 'bg-blue-100 dark:bg-blue-700/10 ring-1 ring-blue-900/30 dark:ring-blue-100/10',
    'red' => 'bg-red-100 dark:bg-red-700/10 ring-1 ring-red-900/30 dark:ring-red-100/10',
];
@endphp

<div data-side="{{ $side }}" aria-label="{{ $athlete->getAriaLabel() }}" @class([
    'flex content-center items-center justify-between h-16 py-2 px-3 shadow-sm rounded-lg',
    'text-gray-900/50 dark:text-gray-50/50 line-through' => $athlete->status->isLose(),
    'text-gray-900 dark:text-gray-50' => $athlete->canProceed(),
    $styles[$side]
])>
    @if ($athlete->isPerson)
        <div class="flex flex-col content-center justify-items-center">
            <span
                class="party-name font-bold leading-7 truncate max-w-[160px]"
                :title="$athlete->display"
            >{{ $athlete->display }}</span>

            <span
                class="party-continent leading-7 truncate max-w-[160px] text-sm text-gray-500"
                :title="$athlete->continentName"
            >{{ $athlete->continentName }}</span>
        </div>

        <span
            class="flex content-center items-center justify-center size-6 rounded {{ $styles[$side] }}"
        >{{ $athlete->drawNumber }}</span>
    @else
        <div class="flex flex-col content-center justify-items-center">
            <span class="party-name font-bold leading-7">{{ $athlete->display }}</span>
        </div>
    @endif
</div>
