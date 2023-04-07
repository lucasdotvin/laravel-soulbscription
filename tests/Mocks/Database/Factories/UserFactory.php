<?php

namespace Tests\Mocks\Database\Factories;

use Orchestra\Testbench\Factories\UserFactory as OrchestraUserFactory;
use Tests\Mocks\Models\User;

class UserFactory extends OrchestraUserFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;
}
