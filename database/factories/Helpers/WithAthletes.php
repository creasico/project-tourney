<?php

namespace Database\Factories\Helpers;

use App\Models\Classification;
use App\Models\Person;
use Database\Factories\ClassificationFactory;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @mixin \Illuminate\Database\Eloquent\Factories\Factory<TModel>
 */
trait WithAthletes
{
    /**
     * @param  \Closure(array, TModel)|array  $state
     */
    public function withAthletes(
        ?int $count = null,
        \Closure|array $state = [],
        ClassificationFactory|Classification|null $classifiedAs = null
    ): static {
        $person = Person::factory($count)->asAthlete(
            withClassification: $this instanceof ClassificationFactory ? false : $classifiedAs
        );

        if (property_exists($this, 'gender')) {
            $person = $person->withGender(self::$gender);
        }

        return $this->has(
            $person->state($state),
            'athletes'
        );
    }
}
