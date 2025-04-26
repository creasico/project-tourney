<?php

namespace Database\Factories\Helpers;

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
    public function withAthletes(?int $count = null, \Closure|array $state = [])
    {
        $person = Person::factory($count)->asAthlete(createClass: ! ($this instanceof ClassificationFactory));

        if (property_exists($this, 'gender')) {
            $person = $person->withGender(self::$gender);
        }

        return $this->has(
            $person->state($state),
            'athletes'
        );
    }
}
