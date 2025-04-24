<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AgeRange;
use App\Enums\Gender;
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

    protected static string $builder = Builders\ClassificationBuilder::class;

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'gender' => Gender::class,
            'age_range' => AgeRange::class,
        ];
    }

    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, MatchGroup::class, 'class_id')
            ->withPivot(['id', 'division', 'attr'])
            ->as('group');
    }

    public function athletes(): HasMany
    {
        return $this->hasMany(Person::class, 'class_id');
    }

    public function display(): Attribute
    {
        return Attribute::get(fn () => implode(' ', [
            $this->label,
            $this->age_range->label(),
            $this->gender->label(),
        ]));
    }
}
