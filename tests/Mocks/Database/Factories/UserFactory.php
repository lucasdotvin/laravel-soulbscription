<?php

namespace LucasDotVin\Soulbscription\Tests\Mocks\Database\Factories;

use LucasDotVin\Soulbscription\Tests\Mocks\Models\User;
use Orchestra\Testbench\Factories\UserFactory as OrchestraUserFactory;

class UserFactory extends OrchestraUserFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;
}
