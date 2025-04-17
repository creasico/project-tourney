<?php

namespace App\Models;

use App\Enums\ClassificationTerm;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Classification extends Model
{
    /** @use HasFactory<\Database\Factories\ClassificationFactory> */
    use HasFactory, HasUlids;

    protected static string $builder = Builders\ClassificationBuilder::class;

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'term' => ClassificationTerm::class,
        ];
    }

    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, TournamentDivision::class, 'class_id')
            ->withPivot(['division', 'attr'])
            ->as('division');
    }
}
