<?php

namespace App\Filament\Imports;

use App\Enums\Gender;
use App\Enums\ParticipantRole;
use App\Models\Participant;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\Rule;

class ParticipantImporter extends Importer
{
    protected static ?string $model = Participant::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('continent')
                ->relationship(resolveUsing: ['name'])
                ->label(trans('continent.singular'))
                ->rules(['required']),
            ImportColumn::make('age')
                ->relationship(resolveUsing: ['label'])
                ->label(trans('classification.term.age'))
                ->rules(['required']),
            ImportColumn::make('weight')
                ->relationship(resolveUsing: ['label'])
                ->label(trans('classification.term.weight'))
                ->rules(['required']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('gender')
                ->rules([Rule::enum(Gender::class)]),
            ImportColumn::make('role')
                ->numeric()
                ->rules([Rule::enum(ParticipantRole::class)]),
        ];
    }

    public function resolveRecord(): ?Participant
    {
        return Participant::query()->firstOrNew([
            'name' => $this->data['name'],
            'gender' => $this->data['gender'],
            'role' => $this->data['role'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your participant import has completed and '.number_format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
