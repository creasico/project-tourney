<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AgeRange;
use App\Enums\Category;
use App\Enums\Gender;
use App\Models\Builders\PersonBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classification extends Model
{
    /** @use HasFactory<\Database\Factories\ClassificationFactory> */
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'gender' => Gender::class,
            'category' => Category::class,
            'age_range' => AgeRange::class,
        ];
    }

    /**
     * @return BelongsToMany<Tournament, Classification, MatchGroup, 'group'>
     */
    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, MatchGroup::class, 'class_id')
            ->withPivot(['id', 'division', 'attr'])
            ->as('group');
    }

    /**
     * @return HasMany<Person, Classification>
     */
    public function athletes(): HasMany|PersonBuilder
    {
        return $this->hasMany(Person::class, 'class_id');
    }

    /**
     * @return HasMany<Matchup, Classification>
     */
    public function matches(): HasMany
    {
        return $this->hasMany(Matchup::class, 'class_id');
    }

    public function hasStarted(): Attribute
    {
        return Attribute::get(fn (): bool => $this->matches->some->is_started);
    }

    public function haveMatchStarted(Tournament $tournament, MatchGroup $group)
    {
        $this->matches();
    }

    public function display(): Attribute
    {
        return Attribute::get(fn (): string => implode(' ', [
            $this->label,
            $this->age_range?->label(),
            $this->gender->label(),
        ]));
    }
}
