<?php

namespace App\Models\Builders;

use App\Enums\ClassificationTerm;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin \App\Models\Classification
 */
class ClassificationBuilder extends Builder
{
    public function onlyAges()
    {
        return $this->where('term', ClassificationTerm::Age);
    }

    public function onlyWeights()
    {
        return $this->where('term', ClassificationTerm::Weight);
    }
}
