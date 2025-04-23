<?php

namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin \App\Models\Classification
 */
class ClassificationBuilder extends Builder
{
    /**
     * @deprecated
     */
    public function onlyAges()
    {
        return $this;
    }

    /**
     * @deprecated
     */
    public function onlyWeights()
    {
        return $this;
    }
}
