<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Continent extends Model
{
    /** @use HasFactory<\Database\Factories\ContinentFactory> */
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'attr' => AsArrayObject::class,
        ];
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }
}
