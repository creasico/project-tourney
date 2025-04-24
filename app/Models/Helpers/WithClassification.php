<?php

declare(strict_types=1);

namespace App\Models\Helpers;

use App\Models\Classification;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait WithClassification
{
    public function classification()
    {
        return $this->belongsTo(Classification::class, 'class_id');
    }
}
