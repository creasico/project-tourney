<?php

namespace Database\Factories\Helpers;

use App\Models\Person;

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
        return $this->has(
            Person::factory($count)->asAthlete()->state($state),
            'athletes'
        );
    }
}
