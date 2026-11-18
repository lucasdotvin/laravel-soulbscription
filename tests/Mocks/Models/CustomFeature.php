<?php

namespace Tests\Mocks\Models;

use LucasDotVin\Soulbscription\Database\Factories\FeatureFactory;
use LucasDotVin\Soulbscription\Models\Feature;

/**
 * Stands in for an application-provided feature model, as configured through
 * `soulbscription.models.feature`.
 */
class CustomFeature extends Feature
{
    protected $table = 'features';

    /**
     * Keep the package's column names, as an overriding model is expected to
     * reuse the published migrations.
     */
    public function getForeignKey()
    {
        return 'feature_id';
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return FeatureFactory::new();
    }
}
