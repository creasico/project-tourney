<?php

namespace App\Models\Helpers;

use App\Models\Builders\ClassificationBuilder;
use App\Models\Classification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait WithClassification
{
    protected function classification(string $field): BelongsTo|ClassificationBuilder
    {
        return $this->belongsTo(Classification::class, $field);
    }

    public function weight(): BelongsTo|ClassificationBuilder
    {
        return $this->classification('class_weight_id')->onlyWeights();
    }

    public function age(): BelongsTo|ClassificationBuilder
    {
        return $this->classification('class_age_id')->onlyAges();
    }
}
