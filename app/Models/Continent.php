<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Person> $athletes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Person> $managers
 */
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

    public function members(): HasMany|Builders\PersonBuilder
    {
        return $this->hasMany(Person::class);
    }

    public function athletes(): HasMany|Builders\PersonBuilder
    {
        return $this->members()->onlyAthletes();
    }

    public function managers(): HasMany|Builders\PersonBuilder
    {
        return $this->members()->onlyManagers();
    }
}
