<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClassificationResource\Pages;

use App\Filament\Resources\ClassificationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClassification extends CreateRecord
{
    protected static string $resource = ClassificationResource::class;
}
